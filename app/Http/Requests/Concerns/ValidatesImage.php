<?php

namespace App\Http\Requests\Concerns;

trait ValidatesImage
{
    protected function imageRules(bool $required = false): array
    {
        $rule = ($required ? 'required' : 'nullable').'|image|mimes:jpeg,png,jpg,gif|max:2048';

        return ['image' => $rule];
    }
}
