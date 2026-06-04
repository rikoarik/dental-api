<?php

namespace App\Models;

use App\Models\Concerns\GeneratesUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class News extends Model implements HasMedia
{
    use GeneratesUniqueSlug;
    use InteractsWithMedia;

    protected $fillable = ['title', 'slug', 'content', 'view_count', 'like_count', 'is_published'];

    public function getCoverImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('cover_image');
    }

    protected $appends = ['cover_image_url'];
}
