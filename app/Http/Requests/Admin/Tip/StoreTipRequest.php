<?php

namespace App\Http\Requests\Admin\Tip;

use App\Http\Requests\Concerns\ValidatesImage;
use Illuminate\Foundation\Http\FormRequest;

class StoreTipRequest extends FormRequest
{
    use ValidatesImage;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'content' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ], $this->imageRules(required: false));
    }
}
