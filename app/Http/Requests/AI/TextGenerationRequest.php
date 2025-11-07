<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Text Generation Request
 *
 * Validates text generation requests including captions, hashtags, and content plans.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class TextGenerationRequest extends FormRequest
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
            'provider' => 'nullable|string|in:auto,openai,anthropic,google,local',
            'model' => 'nullable|string|max:100',
            'temperature' => 'nullable|numeric|between:0,2',
            'max_tokens' => 'nullable|integer|between:1,4000',
        ];

        // Add specific rules based on the request type
        if ($this->is('api/ai/text/caption')) {
            $rules['description'] = 'required|string|max:2000';
            $rules['style'] = 'nullable|string|in:motivational,casual,professional,funny,educational';
        } elseif ($this->is('api/ai/text/hashtags')) {
            $rules['content'] = 'required|string|max:2000';
            $rules['count'] = 'nullable|integer|between:1,30';
        } elseif ($this->is('api/ai/text/plan')) {
            $rules['brief'] = 'required|array';
            $rules['brief.industry'] = 'required|string|max:100';
            $rules['brief.target_audience'] = 'required|string|max:200';
            $rules['brief.brand_voice'] = 'nullable|string|max:100';
            $rules['brief.goals'] = 'nullable|string|max:500';
            $rules['days'] = 'nullable|integer|between:1,30';
        } elseif ($this->is('api/ai/text/generate')) {
            $rules['prompt'] = 'required|string|max:4000';
        } elseif ($this->is('api/ai/text/moderate')) {
            $rules['content'] = 'required|string|max:4000';
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
            'description.required' => 'A description is required to generate a caption.',
            'description.max' => 'The description cannot exceed 2000 characters.',
            'content.required' => 'Content is required for this operation.',
            'content.max' => 'The content cannot exceed 4000 characters.',
            'prompt.required' => 'A prompt is required for text generation.',
            'prompt.max' => 'The prompt cannot exceed 4000 characters.',
            'brief.required' => 'A brief is required to generate a content plan.',
            'brief.industry.required' => 'Industry is required in the brief.',
            'brief.target_audience.required' => 'Target audience is required in the brief.',
            'provider.in' => 'The selected provider is not supported.',
            'style.in' => 'The selected style is not supported.',
            'temperature.between' => 'Temperature must be between 0 and 2.',
            'max_tokens.between' => 'Max tokens must be between 1 and 4000.',
            'count.between' => 'Hashtag count must be between 1 and 30.',
            'days.between' => 'Plan days must be between 1 and 30.',
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
            'description' => 'content description',
            'content' => 'text content',
            'prompt' => 'generation prompt',
            'brief' => 'content brief',
            'brief.industry' => 'industry',
            'brief.target_audience' => 'target audience',
            'brief.brand_voice' => 'brand voice',
            'brief.goals' => 'goals',
            'provider' => 'AI provider',
            'model' => 'AI model',
            'style' => 'caption style',
            'temperature' => 'response temperature',
            'max_tokens' => 'maximum tokens',
            'count' => 'hashtag count',
            'days' => 'plan days',
        ];
    }
}
