<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_bookmark_and_list_articles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $article = Article::create([
            'title' => 'Saved Art',
            'slug' => 'saved-art',
            'content' => 'Content',
            'is_published' => true,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/public/bookmarks/saved-art')
            ->assertStatus(201);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/public/bookmarks')
            ->assertStatus(200)
            ->assertJsonPath('data.0.slug', 'saved-art');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/public/bookmarks?page=1&per_page=10')
            ->assertStatus(200)
            ->assertJsonPath('data.data.0.slug', 'saved-art');
    }

    public function test_user_can_remove_bookmark(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $article = Article::create([
            'title' => 'Saved Art',
            'slug' => 'saved-art',
            'content' => 'Content',
            'is_published' => true,
        ]);

        $user->bookmarkedArticles()->attach($article->id);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/public/bookmarks/saved-art')
            ->assertStatus(200);

        $this->assertDatabaseMissing('bookmarks', [
            'user_id' => $user->id,
            'article_id' => $article->id,
        ]);
    }

    public function test_guest_cannot_access_bookmarks(): void
    {
        $this->getJson('/api/v1/public/bookmarks')->assertStatus(401);
    }
}
