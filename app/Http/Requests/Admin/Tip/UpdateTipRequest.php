<?php

namespace App\Http\Requests\Admin\Tip;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['sometimes', 'required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
