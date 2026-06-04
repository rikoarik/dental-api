<?php

namespace App\Http\Requests\Admin\Banner;

use App\Http\Requests\Concerns\ValidatesImage;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    use ValidatesImage;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'link_url' => ['nullable', 'url'],
            'is_active' => ['sometimes', 'boolean'],
        ], $this->imageRules(required: false));
    }
}
