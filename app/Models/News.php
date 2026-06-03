<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class News extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['title', 'slug', 'content', 'view_count', 'like_count', 'is_published'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title) . '-' . time();
            }
        });
    }

    public function getCoverImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('cover_image');
    }

    protected $appends = ['cover_image_url'];
}