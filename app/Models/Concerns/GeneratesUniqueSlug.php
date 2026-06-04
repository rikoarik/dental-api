<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait GeneratesUniqueSlug
{
    protected static function bootGeneratesUniqueSlug(): void
    {
        static::creating(function ($model) {
            if (! empty($model->slug)) {
                return;
            }

            $source = $model->getSlugSource();
            $model->slug = Str::slug($source).'-'.uniqid();
        });
    }

    protected function getSlugSource(): string
    {
        return $this->title ?? $this->name ?? '';
    }
}
