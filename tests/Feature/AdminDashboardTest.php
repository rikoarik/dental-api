<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\News;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_stats(): void
    {
        $user = $this->createAdminUser();

        News::create(['title' => 'News', 'slug' => 'news-1', 'content' => 'C', 'is_published' => true]);
        Article::create(['title' => 'Art', 'slug' => 'art-1', 'content' => 'C', 'is_published' => true]);
        Product::create(['name' => 'Prod', 'slug' => 'prod-1', 'is_active' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'stats' => [
                        'news_count',
                        'articles_count',
                        'products_count',
                        'active_admins_count',
                    ],
                    'recent_content',
                ],
            ])
            ->assertJsonPath('data.stats.news_count', 1)
            ->assertJsonPath('data.stats.articles_count', 1)
            ->assertJsonPath('data.stats.products_count', 1);
    }
}
