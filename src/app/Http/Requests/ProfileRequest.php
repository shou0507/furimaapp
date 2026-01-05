<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'avatar' => ['nullable', 'mimes:jpeg,png'],

            'name' => ['required', 'string', 'max:20'],

            'postal_code' => ['required', 'regex:/^\d{3}-\d{4}$/'],

            'address' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'avatar.mimes' => 'プロフィール画像はjpegまたはpng形式でアップロードしてください。',

            'name.required' => 'ユーザー名は必須です。',
            'name.max' => 'ユーザー名は20文字以内で入力してください。',

            'postal_code.required' => '郵便番号は必須です。',
            'postal_code.regex' => '郵便番号は「123-4567」の形式で入力してください。',

            'address.required' => '住所は必須です ',
        ];
    }
}
