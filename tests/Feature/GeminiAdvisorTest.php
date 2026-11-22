<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiAdvisorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_advisor(): void
    {
        $response = $this->get(route('advisor.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_advisor_interface_with_context(): void
    {
        $user = User::factory()->create(['name' => 'Ahmad Jamal']);

        Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Utama',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 4500000,
        ]);

        $response = $this->actingAs($user)->get(route('advisor.index'));

        $response->assertOk();
        $response->assertViewIs('advisor.index');
        $response->assertSee('Si Jamal Advisor');
        $response->assertSee('Gemini AI');
        $response->assertSee('Rp 4.500.000');
        $response->assertSee('Si Jamal AI Advisor');
    }

    public function test_user_can_ask_advisor_and_receive_mocked_gemini_advice(): void
    {
        config()->set('services.gemini.api_key', 'mock-gemini-key');

        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Dompet Cash',
            'type' => 'cash',
            'currency' => 'IDR',
            'balance' => 1000000,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Ngopi',
            'type' => 'expense',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 150000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Kopi Harian',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Berdasarkan data keuanganmu, kamu mengeluarkan Rp 150.000 untuk kategori Ngopi bulan ini. Saran saya: kurangi frekuensi nongkrong menjadi 2 kali seminggu untuk menghemat Rp 300.000 per bulan.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->post(route('advisor.ask'), [
                'message' => 'Bulan ini aku paling boros di mana?',
            ]);

        $response->assertRedirect(route('advisor.index'));

        // Check session contains user message and AI reply
        $chat = session('advisor_chat');
        $this->assertIsArray($chat);
        $this->assertGreaterThanOrEqual(2, count($chat));

        $lastMessage = end($chat);
        $this->assertEquals('model', $lastMessage['role']);
        $this->assertStringContainsString('kategori Ngopi', $lastMessage['text']);
    }

    public function test_advisor_ajax_request_returns_json_response(): void
    {
        config()->set('services.gemini.api_key', 'mock-gemini-key');

        $user = User::factory()->create();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Kondisi arus kas kamu sangat baik! Tetap pertahankan tabungan rutin.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('advisor.ask'), [
                'message' => 'Bagaimana kondisi keuanganku?',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'reply' => 'Kondisi arus kas kamu sangat baik! Tetap pertahankan tabungan rutin.',
        ]);
    }

    public function test_advisor_falls_back_to_local_analysis_when_api_key_is_missing(): void
    {
        config()->set('services.gemini.api_key', '');

        $user = User::factory()->create();

        Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 2000000,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('advisor.ask'), [
                'message' => 'Analisis diagnosa kesehatan keuanganku saat ini',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'reply']);
        $this->assertStringContainsString('Laporan Diagnosa Kesehatan Keuanganmu', $response->json('reply'));
    }

    public function test_user_can_reset_advisor_chat_session(): void
    {
        $user = User::factory()->create();

        session(['advisor_chat' => [
            ['role' => 'user', 'text' => 'Halo'],
            ['role' => 'model', 'text' => 'Hai'],
        ]]);

        $response = $this->actingAs($user)->post(route('advisor.reset'));

        $response->assertRedirect(route('advisor.index'));
        $this->assertNull(session('advisor_chat'));
    }
}
