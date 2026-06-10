<?php

namespace Tests\Feature;

use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_active_faqs(): void
    {
        Faq::create([
            'question' => 'Q1',
            'answer' => 'A1',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        Faq::create([
            'question' => 'Q2',
            'answer' => 'A2',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/public/faqs');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_crud_faqs(): void
    {
        $user = $this->createAdminUser();

        $create = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/admin/faqs', [
                'question' => 'Bagaimana cara daftar?',
                'answer' => 'Klik tombol daftar di aplikasi.',
                'sort_order' => 1,
                'is_active' => true,
            ]);

        $create->assertStatus(201);
        $faqId = $create->json('data.id');

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/admin/faqs/{$faqId}", [
                'question' => 'Cara daftar?',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.question', 'Cara daftar?');

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/admin/faqs/{$faqId}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('faqs', ['id' => $faqId]);
    }
}
