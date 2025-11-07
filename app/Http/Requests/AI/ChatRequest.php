<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Chat Request
 *
 * Validates chat-related requests for AI interactions.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class ChatRequest extends FormRequest
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
        return [
            'message' => 'required|string|max:4000',
            'provider' => 'nullable|string|in:auto,openai,anthropic,google,local',
            'model' => 'nullable|string|max:100',
            'temperature' => 'nullable|numeric|between:0,2',
            'max_tokens' => 'nullable|integer|between:1,4000',
            'stream' => 'nullable|boolean',
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
            'message.required' => 'A message is required to start a chat.',
            'message.max' => 'The message cannot exceed 4000 characters.',
            'provider.in' => 'The selected provider is not supported.',
            'temperature.between' => 'Temperature must be between 0 and 2.',
            'max_tokens.between' => 'Max tokens must be between 1 and 4000.',
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
            'message' => 'chat message',
            'provider' => 'AI provider',
            'model' => 'AI model',
            'temperature' => 'response temperature',
            'max_tokens' => 'maximum tokens',
        ];
    }
}
