<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'user_id' => 'exists:users,id',
            'category_id' => 'exists:categories,id',
            'title' => 'string|max:255',
            'body' => 'string',
            'featured_image' => 'nullable|string',
            'status' => 'in:draft,published,scheduled',
            'scheduled_at' => 'nullable|date',
        ];
    }
}
