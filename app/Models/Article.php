<?php

namespace App\Models;

use App\Enums\ContentCategory;
use App\Models\Concerns\GeneratesUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Article extends Model implements HasMedia
{
    use GeneratesUniqueSlug;
    use InteractsWithMedia;

    protected $hidden = ['media'];

    protected $fillable = [
        'title',
        'slug',
        'category',
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

    public function bookmarkedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmarks')->withTimestamps();
    }

    public function getCoverImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('cover_image');
    }

    protected $appends = ['cover_image_url'];
}
