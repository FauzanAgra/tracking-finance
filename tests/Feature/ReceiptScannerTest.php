<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ReceiptScan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReceiptScannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_upload_gemini_extraction_and_transaction_confirmation_flow(): void
    {
        $this->assertTrue(Schema::hasTable('receipt_scans'));

        Storage::fake('public');
        config()->set('services.gemini.api_key', 'test-gemini-key');
        config()->set('services.gemini.model', 'gemini-2.5-flash');

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Utama',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 500000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Belanja Bulanan',
            'type' => 'expense',
        ]);

        $geminiPayload = [
            'merchant_name' => 'Indomaret Point',
            'total_amount' => 78500,
            'transaction_date' => '2026-11-16',
            'suggested_category' => 'Belanja Bulanan',
            'items' => [
                ['name' => 'Roti Gandum', 'price' => 22000],
                ['name' => 'Susu UHT 1L', 'price' => 28500],
                ['name' => 'Kopi Botol', 'price' => 28000],
            ],
        ];

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($geminiPayload)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->actingAs($user)
            ->get(route('receipts.index'))
            ->assertOk();

        $uploadResponse = $this->actingAs($user)
            ->post(route('receipts.store'), [
                'receipt' => UploadedFile::fake()->image('struk.jpg', 400, 600),
            ]);

        $scan = ReceiptScan::where('user_id', $user->id)->firstOrFail();
        $uploadResponse->assertRedirect(route('receipts.show', $scan));
        $this->assertEquals('processed', $scan->status);
        $this->assertEquals('Indomaret Point', $scan->raw_gemini_response['merchant_name']);
        Storage::disk('public')->assertExists($scan->file_path);

        // Verification page renders extracted items
        $this->actingAs($user)
            ->get(route('receipts.show', $scan))
            ->assertOk()
            ->assertSee('Indomaret Point')
            ->assertSee('Roti Gandum');

        // Other user cannot view or confirm
        $this->actingAs($otherUser)
            ->get(route('receipts.show', $scan))
            ->assertForbidden();

        // Confirm & create expense transaction
        $this->actingAs($user)
            ->post(route('receipts.confirm', $scan), [
                'wallet_id' => $wallet->id,
                'category_id' => $category->id,
                'amount' => 78500,
                'transaction_date' => '2026-11-16',
                'description' => 'Indomaret Point',
            ])
            ->assertRedirect(route('transactions.index'));

        $this->assertEquals('421500.0000', $wallet->fresh()->balance);
        $scan->refresh();
        $this->assertNotNull($scan->generated_transaction_id);

        $txn = Transaction::findOrFail($scan->generated_transaction_id);
        $this->assertEquals($scan->file_path, $txn->attachment_path);
    }

    public function test_missing_gemini_api_key_marks_scan_as_failed_gracefully(): void
    {
        Storage::fake('public');
        config()->set('services.gemini.api_key', '');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('receipts.store'), [
                'receipt' => UploadedFile::fake()->image('struk.png'),
            ])
            ->assertRedirect(route('receipts.index'))
            ->assertSessionHas('error');

        $scan = ReceiptScan::where('user_id', $user->id)->firstOrFail();
        $this->assertEquals('failed', $scan->status);
    }
}
