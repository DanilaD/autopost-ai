<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Image Edit Request
 *
 * Validates image editing requests including image editing and variations.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class ImageEditRequest extends FormRequest
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
        $rules = [
            'image' => 'required|file|image|max:10240', // 10MB max
            'provider' => 'nullable|string|in:openai',
            'model' => 'nullable|string|max:100',
            'size' => 'nullable|string|in:1024x1024,1024x1792,1792x1024',
        ];

        // Add prompt validation for image editing
        if ($this->is('api/ai/image/edit')) {
            $rules['prompt'] = 'required|string|max:1000';
        }

        // Add count validation for image variations
        if ($this->is('api/ai/image/variations')) {
            $rules['count'] = 'nullable|integer|between:1,4';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.required' => 'An image file is required.',
            'image.file' => 'The uploaded file must be a valid file.',
            'image.image' => 'The uploaded file must be an image.',
            'image.max' => 'The image file cannot exceed 10MB.',
            'prompt.required' => 'A prompt is required to edit the image.',
            'prompt.max' => 'The prompt cannot exceed 1000 characters.',
            'provider.in' => 'The selected provider is not supported for image editing.',
            'size.in' => 'The selected size is not supported.',
            'count.between' => 'Variation count must be between 1 and 4.',
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
            'prompt' => 'edit prompt',
            'provider' => 'AI provider',
            'model' => 'AI model',
            'size' => 'image size',
            'count' => 'variation count',
        ];
    }
}
