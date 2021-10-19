<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'items' => 'required|array',
            'items.*' => 'required|array:id,count',
            'items.*.id' => 'required|exists:items,id',
            'items.*.count' => 'required|integer|min:1|max:65535'
        ];
    }
}
