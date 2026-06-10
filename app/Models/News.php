<?php

namespace App\Models;

use App\Enums\ContentCategory;
use App\Models\Concerns\GeneratesUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class News extends Model implements HasMedia
{
    use GeneratesUniqueSlug;
    use InteractsWithMedia;

    protected $hidden = ['media'];

    protected $fillable = [
        'title',
        'slug',
        'category',
        'summary',
        'content',
        'view_count',
        'like_count',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'category' => ContentCategory::class,
            'is_published' => 'boolean',
        ];
    }

    public function getCoverImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('cover_image');
    }

    protected $appends = ['cover_image_url'];
}
