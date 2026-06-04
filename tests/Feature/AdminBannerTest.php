<?php

namespace Tests\Feature;

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_banner_with_valid_data()
    {
        Storage::fake('public');
        $user = $this->createAdminUser();
        $file = UploadedFile::fake()->image('banner.jpg')->size(100); // 100 KB

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/admin/banners', [
            'title' => 'New Banner',
            'is_active' => true,
            'image' => $file
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('banners', ['title' => 'New Banner']);
        
        $banner = Banner::first();
        $this->assertNotEmpty($banner->getFirstMediaUrl('banner_image'));
    }

    public function test_banner_creation_fails_without_image()
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/admin/banners', [
            'title' => 'Banner without image',
            'is_active' => true,
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['data' => ['errors' => ['image']]]);
    }

    public function test_banner_creation_fails_with_invalid_file_type()
    {
        Storage::fake('public');
        $user = $this->createAdminUser();
        
        // Memalsukan file script PHP sebagai gambar
        $file = UploadedFile::fake()->create('malicious.php', 10, 'application/x-httpd-php');

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/admin/banners', [
            'title' => 'Malicious Banner',
            'is_active' => true,
            'image' => $file
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['data' => ['errors' => ['image']]]);
    }

    public function test_banner_creation_fails_with_giant_file()
    {
        Storage::fake('public');
        $user = $this->createAdminUser();
        
        // Memalsukan gambar ukuran 3MB (Max 2MB)
        $file = UploadedFile::fake()->image('giant.jpg')->size(3000);

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/admin/banners', [
            'title' => 'Giant Banner',
            'is_active' => true,
            'image' => $file
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['data' => ['errors' => ['image']]]);
    }

    public function test_admin_can_update_banner_without_changing_image()
    {
        Storage::fake('public');
        $user = $this->createAdminUser();
        $banner = Banner::create(['title' => 'Old Title', 'is_active' => true]);
        
        $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/admin/banners/{$banner->id}", [
            'title' => 'Updated Title',
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'title' => 'Updated Title',
            'is_active' => false
        ]);
    }

    public function test_admin_can_delete_banner_and_its_media()
    {
        Storage::fake('public');
        $user = $this->createAdminUser();
        $banner = Banner::create(['title' => 'To Delete', 'is_active' => true]);
        
        $file = UploadedFile::fake()->image('banner.jpg');
        $banner->addMedia($file)->toMediaCollection('banner_image');
        
        $mediaCountBefore = $banner->getMedia('banner_image')->count();
        $this->assertEquals(1, $mediaCountBefore);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/admin/banners/{$banner->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }
    
    public function test_show_banner_fails_on_nonexistent_id()
    {
        $user = $this->createAdminUser();
        
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/admin/banners/9999");
        $response->assertStatus(404);
    }
}
