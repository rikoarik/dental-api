<?php

namespace App\Http\Requests\Admin\Product;

use App\Http\Requests\Concerns\ValidatesImage;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    use ValidatesImage;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'usage_instructions' => ['nullable', 'string'],
            'dosage' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ], $this->imageRules(required: false));
    }
}
