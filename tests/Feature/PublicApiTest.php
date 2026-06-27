<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Banner;
use App\Models\News;
use App\Models\Product;
use App\Models\Tip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_banners_returns_active_banners(): void
    {
        Banner::create(['title' => 'Promo 1', 'is_active' => true]);
        Banner::create(['title' => 'Inactive', 'is_active' => false]);

        $response = $this->getJson('/api/v1/public/banners');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Promo 1');
    }

    public function test_public_news_list_with_limit(): void
    {
        News::create(['title' => 'News 1', 'slug' => 'news-1', 'content' => 'Content', 'is_published' => true]);
        News::create(['title' => 'News 2', 'slug' => 'news-2', 'content' => 'Content', 'is_published' => true]);

        $response = $this->getJson('/api/v1/public/news?limit=1');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_public_articles_popular_with_limit(): void
    {
        Article::create([
            'title' => 'Popular',
            'slug' => 'popular',
            'content' => 'Content',
            'view_count' => 100,
            'like_count' => 50,
            'is_published' => true,
        ]);
        Article::create([
            'title' => 'Less Popular',
            'slug' => 'less-popular',
            'content' => 'Content',
            'view_count' => 1,
            'like_count' => 0,
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/public/articles?sort=popular&limit=1');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.slug', 'popular');
    }

    public function test_public_tips_today_returns_active_tip(): void
    {
        Tip::create(['content' => 'Sikat gigi 2x sehari', 'is_active' => true]);

        $response = $this->getJson('/api/v1/public/tips/today');

        $response->assertStatus(200)
            ->assertJsonPath('data.content', 'Sikat gigi 2x sehari');
    }

    public function test_public_tips_today_returns_404_when_no_active_tip(): void
    {
        Tip::create(['content' => 'Inactive tip', 'is_active' => false]);

        $this->getJson('/api/v1/public/tips/today')->assertStatus(404);
    }

    public function test_can_view_news_detail_and_view_count_increments(): void
    {
        $news = News::create([
            'title' => 'News 1',
            'slug' => 'news-1',
            'content' => 'Content',
            'view_count' => 0,
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/public/news/news-1');

        $response->assertStatus(200);
        $this->assertEquals(1, $news->fresh()->view_count);
    }

    public function test_can_view_article_detail_and_view_count_increments(): void
    {
        $article = Article::create([
            'title' => 'Art 1',
            'slug' => 'art-1',
            'content' => 'Content',
            'view_count' => 0,
            'like_count' => 0,
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/public/articles/art-1');

        $response->assertStatus(200);
        $this->assertEquals(1, $article->fresh()->view_count);
    }

    public function test_can_like_article(): void
    {
        $article = Article::create([
            'title' => 'Art 1',
            'slug' => 'art-1',
            'content' => 'Content',
            'view_count' => 0,
            'like_count' => 0,
            'is_published' => true,
        ]);

        $response = $this->postJson('/api/v1/public/articles/art-1/like');

        $response->assertStatus(200);
        $this->assertEquals(1, $article->fresh()->like_count);
    }

    public function test_can_filter_products_by_search_and_category(): void
    {
        Product::create([
            'name' => 'Pasta Gigi Sensitif',
            'slug' => 'pasta-gigi-sensitif',
            'category' => 'produk_gigi',
            'description' => 'Deskripsi',
            'is_active' => true,
        ]);
        Product::create([
            'name' => 'Sikat Gigi',
            'slug' => 'sikat-gigi',
            'category' => 'produk_gigi',
            'description' => 'Deskripsi',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/public/products?search=pasta');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Pasta Gigi Sensitif');
    }

    public function test_can_view_product_detail_with_related_articles(): void
    {
        Product::create([
            'name' => 'Sikat Gigi',
            'slug' => 'sikat-gigi',
            'description' => 'Deskripsi',
            'is_active' => true,
        ]);
        Article::create([
            'title' => 'Related',
            'slug' => 'related',
            'content' => 'Content',
            'view_count' => 10,
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/public/products/sikat-gigi');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['product', 'related_articles'],
            ]);
    }

    public function test_viewing_nonexistent_article_returns_404(): void
    {
        $this->getJson('/api/v1/public/articles/ghost-article')->assertStatus(404);
    }

    public function test_liking_nonexistent_news_returns_404(): void
    {
        $this->postJson('/api/v1/public/news/9999/like')->assertStatus(404);
    }

    public function test_unpublished_article_cannot_be_viewed_or_liked(): void
    {
        Article::create([
            'title' => 'Draft Art',
            'slug' => 'draft-art',
            'content' => 'Content',
            'is_published' => false,
        ]);

        $this->getJson('/api/v1/public/articles/draft-art')->assertStatus(404);
        $this->postJson('/api/v1/public/articles/draft-art/like')->assertStatus(404);
    }

    public function test_unpublished_news_cannot_be_liked(): void
    {
        $news = News::create([
            'title' => 'Draft News',
            'slug' => 'draft-news',
            'content' => 'Content',
            'is_published' => false,
        ]);

        $this->postJson('/api/v1/public/news/'.$news->id.'/like')->assertStatus(404);
    }

    public function test_spamming_likes_triggers_rate_limit(): void
    {
        Article::create([
            'title' => 'Spam Art',
            'slug' => 'spam-art',
            'content' => 'Content',
            'view_count' => 0,
            'like_count' => 0,
            'is_published' => true,
        ]);

        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/api/v1/public/articles/spam-art/like');
        }

        $response = $this->postJson('/api/v1/public/articles/spam-art/like');
        $response->assertStatus(429)
            ->assertJson(['status' => false]);
    }
}
