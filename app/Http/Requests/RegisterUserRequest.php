<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;//所有用户都有权限
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'=>'required|min:6',
            'email'=>'required|email|unique:users',
            'phone' => [
                "required","regex:/^1(3|5|7|6|8|4)[\d]{9}$/",
                "unique:users"
            ]
        ];
    }
}
