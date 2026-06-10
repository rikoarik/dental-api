<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Tip extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['content', 'is_active'];

    protected $hidden = ['media'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('tip_image');
    }

    protected $appends = ['image_url'];
}
