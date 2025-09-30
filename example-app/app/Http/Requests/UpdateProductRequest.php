<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'Title' => ['sometimes','required','string','max:255', Rule::unique('products','Title')->ignore($id,'ProductID')],
            'Description' => ['sometimes','nullable','string'],
            'CategoryID' => ['sometimes','required','integer'],
            'Price' => ['sometimes','required','numeric','min:0'],
            'Image' => ['sometimes','nullable','string','max:1024'],
            'image' => ['sometimes','nullable','file','image','max:4096'],
            'StockQuantity' => ['sometimes','nullable','integer','min:0'],
        ];
    }
}
