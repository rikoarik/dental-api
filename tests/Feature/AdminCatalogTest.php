<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Tip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // PRODUCTS TESTS
    // ==========================================
    public function test_admin_can_create_product()
    {
        Storage::fake('public');
        $user = $this->createAdminUser();
        $file = UploadedFile::fake()->image('product.png');

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/admin/products', [
            'name' => 'Obat Kumur',
            'description' => 'Deskripsi obat kumur',
            'usage_instructions' => 'Kumur 2x sehari',
            'dosage' => '10ml',
            'is_active' => true,
            'image' => $file
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => 'Obat Kumur'
        ]);
    }

    public function test_product_validation_fails_when_required_fields_missing()
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/admin/products', [
            // Missing name
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['data' => ['errors' => ['name']]]);
    }

    public function test_admin_cannot_update_nonexistent_product()
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/admin/products/9999', [
            'name' => 'Ghost Product'
        ]);

        $response->assertStatus(404);
    }

    // ==========================================
    // TIPS TESTS
    // ==========================================
    public function test_admin_can_create_tip()
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/admin/tips', [
            'content' => 'Ini tip harian',
            'is_active' => true
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tips', ['content' => 'Ini tip harian']);
    }

    public function test_tip_validation_fails_when_content_empty()
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/admin/tips', [
            'content' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['data' => ['errors' => ['content']]]);
    }

    public function test_admin_can_delete_tip()
    {
        $user = $this->createAdminUser();
        $tip = Tip::create(['content' => 'Tip 1']);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/admin/tips/{$tip->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tips', ['id' => $tip->id]);
    }
}
