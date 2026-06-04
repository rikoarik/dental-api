<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Banner;
use App\Models\News;
use App\Models\Tip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_returns_all_components()
    {
        Banner::create(['title' => 'Promo 1', 'is_active' => true]);
        News::create(['title' => 'News 1', 'slug' => 'news-1', 'content' => 'Content', 'is_published' => true]);
        Tip::create(['content' => 'Sikat gigi 2x sehari', 'is_active' => true]);
        Article::create([
            'title' => 'Art 1', 
            'slug' => 'art-1', 
            'content' => 'Content', 
            'view_count' => 10, 
            'like_count' => 5, 
            'is_published' => true
        ]);

        $response = $this->getJson('/api/v1/public/home');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'banners',
                         'daily_tip',
                         'latest_news',
                         'popular_articles'
                     ]
                 ]);
                 
        $this->assertEquals('Promo 1', $response->json('data.banners.0.title'));
        $this->assertEquals('Sikat gigi 2x sehari', $response->json('data.daily_tip.content'));
    }

    public function test_can_view_article_detail_and_view_count_increments()
    {
        $article = Article::create([
            'title' => 'Art 1', 
            'slug' => 'art-1', 
            'content' => 'Content', 
            'view_count' => 0, 
            'like_count' => 0, 
            'is_published' => true
        ]);

        $response = $this->getJson('/api/v1/public/articles/art-1');

        $response->assertStatus(200);
        $this->assertEquals(1, $article->fresh()->view_count);
    }

    public function test_can_like_article()
    {
        $article = Article::create([
            'title' => 'Art 1', 
            'slug' => 'art-1', 
            'content' => 'Content', 
            'view_count' => 0, 
            'like_count' => 0, 
            'is_published' => true
        ]);

        $response = $this->postJson('/api/v1/public/articles/art-1/like');

        $response->assertStatus(200);
        $this->assertEquals(1, $article->fresh()->like_count);
    }

    public function test_viewing_nonexistent_article_returns_404()
    {
        $response = $this->getJson('/api/v1/public/articles/ghost-article');
        $response->assertStatus(404);
    }

    public function test_liking_nonexistent_news_returns_404()
    {
        $response = $this->postJson('/api/v1/public/news/9999/like');
        $response->assertStatus(404);
    }

    public function test_unpublished_article_cannot_be_viewed_or_liked()
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

    public function test_unpublished_news_cannot_be_liked()
    {
        $news = News::create([
            'title' => 'Draft News',
            'slug' => 'draft-news',
            'content' => 'Content',
            'is_published' => false,
        ]);

        $this->postJson('/api/v1/public/news/'.$news->id.'/like')->assertStatus(404);
    }

    public function test_spamming_likes_triggers_rate_limit()
    {
        $article = Article::create([
            'title' => 'Spam Art', 
            'slug' => 'spam-art', 
            'content' => 'Content', 
            'view_count' => 0, 
            'like_count' => 0, 
            'is_published' => true
        ]);

        // Route memiliki middleware 'throttle:20,1' (20 request per menit)
        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/api/v1/public/articles/spam-art/like');
        }

        // Request ke 21 harus kena rate limit (HTTP 429)
        $response = $this->postJson('/api/v1/public/articles/spam-art/like');
        $response->assertStatus(429)
                 ->assertJson(['status' => false]);
    }
}
