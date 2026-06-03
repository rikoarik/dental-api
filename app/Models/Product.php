<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['name', 'slug', 'description', 'usage_instructions', 'dosage', 'is_active'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name) . '-' . time();
            }
        });
    }

    public function getCoverImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('cover_image');
    }

    protected $appends = ['cover_image_url'];
}