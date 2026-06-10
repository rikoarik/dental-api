<?php

namespace App\Models;

use App\Enums\BannerTag;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Banner extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $hidden = ['media'];

    protected $fillable = [
        'title',
        'subtitle',
        'tag',
        'link_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tag' => BannerTag::class,
            'is_active' => 'boolean',
        ];
    }

    public function getImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('banner_image');
    }

    protected $appends = ['image_url'];
}
