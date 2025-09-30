<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'Title' => ['required','string','max:255','unique:products,Title'],
            'Description' => ['nullable','string'],
            'CategoryID' => ['required','integer'],
            'Price' => ['required','numeric','min:0'],
            'Image' => ['nullable','string','max:1024'],
            'image' => ['nullable','file','image','max:4096'],
            'StockQuantity' => ['nullable','integer','min:0'],
        ];
    }
}
