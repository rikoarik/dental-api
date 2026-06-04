<?php

namespace App\Models;

use App\Models\Concerns\GeneratesUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use GeneratesUniqueSlug;
    use InteractsWithMedia;

    protected $fillable = ['name', 'slug', 'description', 'usage_instructions', 'dosage', 'is_active'];

    public function getProductImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('product_image');
    }

    protected $appends = ['product_image_url'];
}
