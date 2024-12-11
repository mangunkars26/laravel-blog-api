<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostFilterRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan untuk membuat request ini.
     */
    public function authorize()
    {
        return true; // Set ke true jika semua user diizinkan
    }

    /**
     * Tentukan aturan validasi yang berlaku untuk request ini.
     */
    public function rules()
    {
        return [
            'status' => 'in:published,draft',
            'author_id' => 'integer|exists:users,id',
            'category_id' => 'integer|exists:categories,id',
            'date_from' => 'date|before_or_equal:date_to',
            'date_to' => 'date|after_or_equal:date_from',
            'sort_by' => 'in:created_at,updated_at,views',
            'order' => 'in:asc,desc',
            'limit' => 'integer|min:1|max:100',
            'search' => 'string|max:255'
        ];
    }

    /**
     * Kustomisasi pesan error untuk setiap aturan validasi.
     */
    public function messages()
    {
        return [
            'status.in' => 'Status must be either published or draft.',
            'author_id.exists' => 'The specified author does not exist.',
            'category_id.exists' => 'The specified category does not exist.',
            'date_from.date' => 'The start date must be a valid date.',
            'date_to.date' => 'The end date must be a valid date.',
            'sort_by.in' => 'Invalid sort column. Allowed columns: created_at, updated_at, views.',
            'order.in' => 'Order must be either asc or desc.',
            'limit.min' => 'Limit must be at least 1.',
            'limit.max' => 'Limit cannot exceed 100.',
            'search.max' => 'Search query too long, maximum 255 characters allowed.'
        ];
    }
}
