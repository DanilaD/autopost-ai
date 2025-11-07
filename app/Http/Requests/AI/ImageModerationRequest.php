<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Image Moderation Request
 *
 * Validates image moderation requests for content safety checks.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class ImageModerationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'required|file|image|max:10240', // 10MB max
            'provider' => 'nullable|string|in:openai',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.required' => 'An image file is required for moderation.',
            'image.file' => 'The uploaded file must be a valid file.',
            'image.image' => 'The uploaded file must be an image.',
            'image.max' => 'The image file cannot exceed 10MB.',
            'provider.in' => 'The selected provider is not supported for image moderation.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'image' => 'image file',
            'provider' => 'AI provider',
        ];
    }
}
