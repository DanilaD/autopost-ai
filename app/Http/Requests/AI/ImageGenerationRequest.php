<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Image Generation Request
 *
 * Validates image generation requests including single and multiple image generation.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class ImageGenerationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'prompt' => 'required|string|max:1000',
            'provider' => 'nullable|string|in:auto,openai,google',
            'model' => 'nullable|string|max:100',
            'size' => 'nullable|string|in:1024x1024,1024x1792,1792x1024',
            'style' => 'nullable|string|in:vivid,natural',
            'quality' => 'nullable|string|in:standard,hd',
        ];

        // Add count validation for multiple image generation
        if ($this->is('api/ai/image/generate-multiple')) {
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
            'prompt.required' => 'A prompt is required to generate an image.',
            'prompt.max' => 'The prompt cannot exceed 1000 characters.',
            'provider.in' => 'The selected provider is not supported for image generation.',
            'size.in' => 'The selected size is not supported.',
            'style.in' => 'The selected style is not supported.',
            'quality.in' => 'The selected quality is not supported.',
            'count.between' => 'Image count must be between 1 and 4.',
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
            'prompt' => 'image prompt',
            'provider' => 'AI provider',
            'model' => 'AI model',
            'size' => 'image size',
            'style' => 'image style',
            'quality' => 'image quality',
            'count' => 'image count',
        ];
    }
}
