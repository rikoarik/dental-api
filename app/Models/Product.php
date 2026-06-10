<?php

namespace App\Models;

use App\Enums\ProductCategory;
use App\Models\Concerns\GeneratesUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use GeneratesUniqueSlug;
    use InteractsWithMedia;

    protected $hidden = ['media'];

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'benefits',
        'usage_instructions',
        'doctor_tips',
        'dosage',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => ProductCategory::class,
            'benefits' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getProductImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('product_image');
    }

    protected $appends = ['product_image_url'];
}
