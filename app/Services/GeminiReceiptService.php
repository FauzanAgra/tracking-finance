<?php

namespace App\Services;

use App\Models\ReceiptScan;
use App\Models\Transaction;
use App\Models\User;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GeminiReceiptService
{
    public function __construct(
        private readonly TransactionService $transactionService,
    ) {}

    /**
     * Store the uploaded receipt image and extract structured fields via Gemini Vision API.
     */
    public function scanReceipt(User $user, UploadedFile $file): ReceiptScan
    {
        $path = $file->store("receipts/{$user->id}", 'public');

        $scan = ReceiptScan::create([
            'user_id' => $user->id,
            'file_path' => $path,
            'status' => 'pending',
        ]);

        $apiKey = (string) config('services.gemini.api_key', '');
        if ($apiKey === '') {
            $scan->update([
                'status' => 'failed',
                'raw_gemini_response' => ['error' => 'GEMINI_API_KEY belum dikonfigurasi di .env.'],
            ]);

            throw new DomainException('GEMINI_API_KEY belum diatur di file .env. Kamu tetap bisa mengisi transaksi secara manual.');
        }

        $configuredModel = (string) config('services.gemini.model', 'gemini-3.5-flash');
        $candidateModels = array_values(array_unique([
            $configuredModel,
            'gemini-3.5-flash',
            'gemini-3.6-flash',
            'gemini-3.7-flash',
            'gemini-3.8-flash',
        ]));

        $mimeType = $file->getMimeType() ?: 'image/jpeg';
        $base64Image = base64_encode(Storage::disk('public')->get($path));

        $prompt = 'Extract receipt details and return strictly valid JSON with keys: '
            .'merchant_name (string), total_amount (number), transaction_date (YYYY-MM-DD), '
            .'suggested_category (string, e.g. Makanan, Belanja Bulanan, Transportasi), '
            .'and items (array of objects with name and price).';

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Image,
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ],
        ];

        try {
            $response = null;
            $lastException = null;

            foreach ($candidateModels as $model) {
                try {
                    $response = Http::withOptions(['force_ip_resolve' => 'v4'])
                        ->timeout(20)
                        ->withHeaders([
                            'x-goog-api-key' => $apiKey,
                            'Content-Type' => 'application/json',
                        ])
                        ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", $payload);

                    if ($response->successful()) {
                        break;
                    }

                    if (! in_array($response->status(), [404, 429, 500, 503], true)) {
                        break;
                    }
                } catch (Throwable $e) {
                    $lastException = $e;
                    $response = null;
                }
            }

            if (($response === null || ! $response->successful()) && ($response === null || in_array($response->status(), [404, 429, 500, 503], true))) {
                foreach ($this->discoverFlashModels($apiKey) as $discoveredModel) {
                    if (in_array($discoveredModel, $candidateModels, true)) {
                        continue;
                    }

                    try {
                        $response = Http::withOptions(['force_ip_resolve' => 'v4'])
                            ->timeout(20)
                            ->withHeaders([
                                'x-goog-api-key' => $apiKey,
                                'Content-Type' => 'application/json',
                            ])
                            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$discoveredModel}:generateContent", $payload);

                        if ($response->successful() || ! in_array($response->status(), [404, 429, 500, 503], true)) {
                            break;
                        }
                    } catch (Throwable $e) {
                        $lastException = $e;
                        $response = null;
                    }
                }
            }

            if ($response === null && $lastException !== null) {
                throw $lastException;
            }

            if ($response === null || ! $response->successful()) {
                $status = $response?->status() ?? 0;
                $apiMessage = (string) data_get($response?->json(), 'error.message', '');
                $errorDetail = $apiMessage !== '' ? "HTTP {$status}: {$apiMessage}" : "HTTP {$status}";

                $scan->update([
                    'status' => 'failed',
                    'raw_gemini_response' => ['error' => $errorDetail],
                ]);

                throw new DomainException("Gagal memindai struk dengan Gemini API ({$errorDetail}).");
            }

            $textPayload = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
            $parsed = $this->parseJsonResponse($textPayload);

            $scan->update([
                'status' => 'processed',
                'raw_gemini_response' => $parsed,
            ]);

            return $scan->refresh();
        } catch (DomainException $e) {
            throw $e;
        } catch (Throwable $e) {
            $scan->update([
                'status' => 'failed',
                'raw_gemini_response' => ['error' => $e->getMessage()],
            ]);

            throw new DomainException('Terjadi kesalahan saat menghubungi Gemini Vision API: '.$e->getMessage());
        }
    }

    /**
     * Confirm extracted receipt data and record an expense transaction atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function confirmAndCreateTransaction(ReceiptScan $scan, User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($scan, $user, $data): Transaction {
            if ($scan->generated_transaction_id !== null) {
                throw new DomainException('Struk ini sudah dikonfirmasi menjadi transaksi.');
            }

            $transaction = $this->transactionService->recordExpense($user, [
                'wallet_id' => $data['wallet_id'],
                'category_id' => $data['category_id'],
                'amount' => $data['amount'],
                'transaction_date' => $data['transaction_date'],
                'description' => $data['description'] ?? null,
                'attachment_path' => $scan->file_path,
            ]);

            $scan->update([
                'generated_transaction_id' => $transaction->id,
                'status' => 'processed',
            ]);

            return $transaction;
        });
    }

    /**
     * Parse JSON text returned by Gemini (stripping markdown fences if present).
     *
     * @return array<string, mixed>
     */
    public function parseJsonResponse(string $text): array
    {
        $cleaned = trim($text);
        $cleaned = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $cleaned) ?? $cleaned;
        $decoded = json_decode($cleaned, true);

        if (! is_array($decoded)) {
            throw new DomainException('Respon Gemini tidak berupa JSON yang valid.');
        }

        return [
            'merchant_name' => (string) ($decoded['merchant_name'] ?? ''),
            'total_amount' => (float) ($decoded['total_amount'] ?? 0),
            'transaction_date' => (string) ($decoded['transaction_date'] ?? now()->toDateString()),
            'suggested_category' => (string) ($decoded['suggested_category'] ?? ''),
            'items' => is_array($decoded['items'] ?? null) ? $decoded['items'] : [],
        ];
    }

    /**
     * Query Gemini ListModels endpoint to discover active generateContent flash models for this API key.
     *
     * @return array<int, string>
     */
    private function discoverFlashModels(string $apiKey): array
    {
        try {
            $response = Http::withOptions(['force_ip_resolve' => 'v4'])
                ->timeout(15)
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->get('https://generativelanguage.googleapis.com/v1beta/models');

            if (! $response->successful()) {
                return [];
            }

            return collect($response->json('models', []))
                ->filter(fn (array $m) => in_array('generateContent', $m['supportedGenerationMethods'] ?? [], true))
                ->map(fn (array $m) => str_replace('models/', '', (string) ($m['name'] ?? '')))
                ->filter(fn (string $name) => $name !== '' && str_contains(strtolower($name), 'flash'))
                ->values()
                ->all();
        } catch (Throwable) {
            return [];
        }
    }
}
