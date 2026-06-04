<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_news_and_slug_is_generated(): void
    {
        Storage::fake('public');
        $user = $this->createAdminUser();
        $file = UploadedFile::fake()->image('news.jpg');

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/admin/news', [
            'title' => 'Klinik Gigi Buka Cabang Baru',
            'content' => 'Isi berita disini...',
            'is_published' => true,
            'image' => $file,
        ]);

        $response->assertStatus(201)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('news', [
            'title' => 'Klinik Gigi Buka Cabang Baru',
        ]);

        $news = News::where('title', 'Klinik Gigi Buka Cabang Baru')->first();
        $this->assertStringStartsWith('klinik-gigi-buka-cabang-baru', $news->slug);
    }

    public function test_admin_can_create_article_and_slug_is_generated(): void
    {
        Storage::fake('public');
        $user = $this->createAdminUser();
        $file = UploadedFile::fake()->image('article.jpg');

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/admin/articles', [
            'title' => 'Cara Merawat Gigi Anak',
            'content' => 'Isi artikel disini...',
            'is_published' => true,
            'image' => $file,
        ]);

        $response->assertStatus(201)
                 ->assertJson(['status' => true]);

        $article = Article::where('title', 'Cara Merawat Gigi Anak')->first();
        $this->assertStringStartsWith('cara-merawat-gigi-anak', $article->slug);
    }

    public function test_updating_article_title_does_not_change_slug(): void
    {
        $user = $this->createAdminUser();
        $article = Article::create([
            'title' => 'Judul Lama',
            'content' => 'Konten',
            'is_published' => true,
        ]);
        $originalSlug = $article->slug;

        $response = $this->actingAs($user, 'sanctum')->putJson(
            '/api/v1/admin/articles/'.$article->id,
            ['title' => 'Judul Baru']
        );

        $response->assertStatus(200);
        $this->assertEquals($originalSlug, $article->fresh()->slug);
    }

    public function test_news_validation_rejects_long_titles(): void
    {
        $user = $this->createAdminUser();
        $longTitle = str_repeat('A', 256);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/admin/news', [
            'title' => $longTitle,
            'content' => 'Content...',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['status' => false])
                 ->assertJsonStructure(['data' => ['errors' => ['title']]]);
    }

    public function test_article_validation_rejects_wrong_data_types(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/admin/articles', [
            'title' => ['Ini', 'Array'],
            'content' => 'Content...',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['status' => false])
                 ->assertJsonStructure(['data' => ['errors' => ['title']]]);
    }

    public function test_duplicate_news_titles_generate_unique_slugs(): void
    {
        Storage::fake('public');
        $user = $this->createAdminUser();

        $this->actingAs($user, 'sanctum')->post('/api/v1/admin/news', [
            'title' => 'Judul Berita',
            'content' => 'Isi berita 1',
            'image' => UploadedFile::fake()->image('news1.jpg'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/admin/news', [
            'title' => 'Judul Berita',
            'content' => 'Isi berita 2',
            'image' => UploadedFile::fake()->image('news2.jpg'),
        ]);

        $response->assertStatus(201);

        $slugs = News::where('title', 'Judul Berita')->pluck('slug');
        $this->assertCount(2, $slugs);
        $this->assertNotEquals($slugs[0], $slugs[1]);
    }

    public function test_user_without_admin_role_cannot_access_admin_crud(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/news');

        $response->assertStatus(403);
    }
}
