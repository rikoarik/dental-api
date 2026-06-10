<?php

namespace App\Http\Requests\Admin\Banner;

use App\Enums\BannerTag;
use App\Http\Requests\Concerns\ValidatesImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBannerRequest extends FormRequest
{
    use ValidatesImage;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', Rule::enum(BannerTag::class)],
            'link_url' => ['nullable', 'url'],
            'is_active' => ['sometimes', 'boolean'],
        ], $this->imageRules(required: true));
    }
}
