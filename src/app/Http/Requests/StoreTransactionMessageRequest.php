<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
{
    return [
        'message' => ['nullable', 'string', 'max:400', 'required_without:image'],
        'image' => ['nullable', 'file', 'mimes:jpeg,png', 'required_without:message'],
    ];
}

public function messages()
{
    return [
        'message.required_without' => '本文または画像を入力してください',
        'image.required_without' => '本文または画像を入力してください',
        'message.max' => '本文は400文字以内で入力してください',
        'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
    ];
}
}
