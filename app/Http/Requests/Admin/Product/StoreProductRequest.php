<?php

namespace App\Http\Requests\Admin\Product;

use App\Enums\ProductCategory;
use App\Http\Requests\Concerns\ValidatesImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    use ValidatesImage;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['sometimes', Rule::enum(ProductCategory::class)],
            'description' => ['nullable', 'string'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string'],
            'usage_instructions' => ['nullable', 'string'],
            'doctor_tips' => ['nullable', 'string'],
            'dosage' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ], $this->imageRules(required: false));
    }
}
