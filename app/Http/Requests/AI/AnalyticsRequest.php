<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Analytics Request
 *
 * Validates analytics and usage tracking requests.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class AnalyticsRequest extends FormRequest
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
        $rules = [];

        // Add specific rules based on the request type
        if ($this->is('api/ai/analytics/company-usage') || $this->is('api/ai/analytics/user-usage')) {
            $rules['start_date'] = 'nullable|date';
            $rules['end_date'] = 'nullable|date|after_or_equal:start_date';
            $rules['provider'] = 'nullable|string|in:openai,anthropic,google,local';
            $rules['type'] = 'nullable|string|in:caption,image,video,plan,hashtags,description,chat';
        } elseif ($this->is('api/ai/analytics/cost-comparison')) {
            $rules['type'] = 'nullable|string|in:text,image';
            $rules['tokens'] = 'nullable|integer|between:1,10000';
            $rules['image_count'] = 'nullable|integer|between:1,10';
        } elseif ($this->is('api/ai/analytics/recent-generations')) {
            $rules['limit'] = 'nullable|integer|between:1,100';
            $rules['type'] = 'nullable|string|in:caption,image,video,plan,hashtags,description,chat';
            $rules['provider'] = 'nullable|string|in:openai,anthropic,google,local';
        } elseif ($this->is('api/ai/analytics/fallback-chain')) {
            $rules['type'] = 'nullable|string|in:text,image';
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
            'start_date.date' => 'Start date must be a valid date.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'provider.in' => 'The selected provider is not supported.',
            'type.in' => 'The selected type is not supported.',
            'tokens.between' => 'Token count must be between 1 and 10000.',
            'image_count.between' => 'Image count must be between 1 and 10.',
            'limit.between' => 'Limit must be between 1 and 100.',
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
            'start_date' => 'start date',
            'end_date' => 'end date',
            'provider' => 'AI provider',
            'type' => 'generation type',
            'tokens' => 'token count',
            'image_count' => 'image count',
            'limit' => 'result limit',
        ];
    }
}
