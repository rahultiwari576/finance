<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'age' => ['required', 'integer', 'min:18', 'max:120'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'aadhar_number' => ['required', 'digits:12', 'unique:users,aadhar_number'],
            'pan_number' => ['required', 'string', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
            // 'aadhar_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            // 'pan_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}

