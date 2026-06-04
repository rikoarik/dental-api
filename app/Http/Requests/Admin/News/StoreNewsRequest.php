<?php

namespace App\Http\Requests\Admin\News;

use App\Http\Requests\Concerns\ValidatesImage;
use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
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
            'content' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ], $this->imageRules(required: false));
    }
}
