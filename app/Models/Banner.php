<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Banner extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['title', 'link_url', 'is_active'];

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('banner_image');
    }

    protected $appends = ['image_url'];
}
