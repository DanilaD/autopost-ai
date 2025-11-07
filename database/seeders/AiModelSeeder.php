<?php

namespace Database\Seeders;

use App\Models\AiModel;
use Illuminate\Database\Seeder;

class AiModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
            // OpenAI Models
            [
                'name' => 'gpt-4o',
                'provider' => 'openai',
                'type' => 'text',
                'cost_per_token' => 0.005,
                'cost_per_image' => null,
                'is_active' => true,
                'capabilities' => ['text_generation', 'chat', 'reasoning', 'analysis'],
                'description' => 'OpenAI GPT-4o - Most capable model with multimodal capabilities',
            ],
            [
                'name' => 'gpt-4o-mini',
                'provider' => 'openai',
                'type' => 'text',
                'cost_per_token' => 0.00015,
                'cost_per_image' => null,
                'is_active' => true,
                'capabilities' => ['text_generation', 'chat', 'reasoning', 'analysis'],
                'description' => 'OpenAI GPT-4o-mini - Faster and cheaper version of GPT-4o',
            ],
            [
                'name' => 'gpt-3.5-turbo',
                'provider' => 'openai',
                'type' => 'text',
                'cost_per_token' => 0.0005,
                'cost_per_image' => null,
                'is_active' => true,
                'capabilities' => ['text_generation', 'chat'],
                'description' => 'OpenAI GPT-3.5-turbo - Fast and efficient text generation',
            ],
            [
                'name' => 'dall-e-3',
                'provider' => 'openai',
                'type' => 'image',
                'cost_per_token' => null,
                'cost_per_image' => 0.04,
                'is_active' => true,
                'capabilities' => ['image_generation', 'art_creation'],
                'description' => 'OpenAI DALL-E 3 - Advanced image generation model',
            ],
            [
                'name' => 'dall-e-2',
                'provider' => 'openai',
                'type' => 'image',
                'cost_per_token' => null,
                'cost_per_image' => 0.02,
                'is_active' => true,
                'capabilities' => ['image_generation', 'art_creation'],
                'description' => 'OpenAI DALL-E 2 - Image generation model',
            ],

            // Anthropic Models (Phase 2 - Active)
            [
                'name' => 'claude-3-opus-20240229',
                'provider' => 'anthropic',
                'type' => 'text',
                'cost_per_token' => 0.015,
                'cost_per_image' => null,
                'is_active' => true, // Phase 2 - Now active
                'capabilities' => ['text_generation', 'chat', 'reasoning', 'analysis'],
                'description' => 'Anthropic Claude 3 Opus - Most capable Claude model',
            ],
            [
                'name' => 'claude-3-sonnet-20240229',
                'provider' => 'anthropic',
                'type' => 'text',
                'cost_per_token' => 0.003,
                'cost_per_image' => null,
                'is_active' => true, // Phase 2 - Now active
                'capabilities' => ['text_generation', 'chat', 'reasoning', 'analysis'],
                'description' => 'Anthropic Claude 3 Sonnet - Balanced performance and cost',
            ],
            [
                'name' => 'claude-3-haiku-20240307',
                'provider' => 'anthropic',
                'type' => 'text',
                'cost_per_token' => 0.00025,
                'cost_per_image' => null,
                'is_active' => true, // Phase 2 - Now active
                'capabilities' => ['text_generation', 'chat'],
                'description' => 'Anthropic Claude 3 Haiku - Fast and efficient',
            ],

            // Google AI Models (Phase 2 - Active)
            [
                'name' => 'gemini-pro',
                'provider' => 'google',
                'type' => 'text',
                'cost_per_token' => 0.0005,
                'cost_per_image' => null,
                'is_active' => true, // Phase 2 - Now active
                'capabilities' => ['text_generation', 'chat', 'reasoning'],
                'description' => 'Google Gemini Pro - Advanced text generation',
            ],
            [
                'name' => 'gemini-pro-vision',
                'provider' => 'google',
                'type' => 'multimodal',
                'cost_per_token' => 0.0005,
                'cost_per_image' => null,
                'is_active' => true, // Phase 2 - Now active
                'capabilities' => ['text_generation', 'image_analysis', 'multimodal'],
                'description' => 'Google Gemini Pro Vision - Multimodal capabilities',
            ],
            [
                'name' => 'imagen-3',
                'provider' => 'google',
                'type' => 'image',
                'cost_per_token' => null,
                'cost_per_image' => 0.02,
                'is_active' => true, // Phase 2 - Now active
                'capabilities' => ['image_generation', 'art_creation'],
                'description' => 'Google Imagen 3 - Advanced image generation',
            ],

            // Local AI Models (Phase 3 - Active)
            [
                'name' => 'llama2',
                'provider' => 'local',
                'type' => 'text',
                'cost_per_token' => 0.0,
                'cost_per_image' => null,
                'is_active' => true, // Phase 3 - Now active
                'capabilities' => ['text_generation', 'chat', 'reasoning'],
                'description' => 'Meta Llama 2 - Open source language model',
            ],
            [
                'name' => 'llama2:7b',
                'provider' => 'local',
                'type' => 'text',
                'cost_per_token' => 0.0,
                'cost_per_image' => null,
                'is_active' => true, // Phase 3 - Now active
                'capabilities' => ['text_generation', 'chat'],
                'description' => 'Meta Llama 2 7B - Smaller, faster version',
            ],
            [
                'name' => 'codellama',
                'provider' => 'local',
                'type' => 'text',
                'cost_per_token' => 0.0,
                'cost_per_image' => null,
                'is_active' => true, // Phase 3 - Now active
                'capabilities' => ['text_generation', 'code_generation', 'chat'],
                'description' => 'Meta CodeLlama - Specialized for code generation',
            ],
            [
                'name' => 'mistral',
                'provider' => 'local',
                'type' => 'text',
                'cost_per_token' => 0.0,
                'cost_per_image' => null,
                'is_active' => true, // Phase 3 - Now active
                'capabilities' => ['text_generation', 'chat', 'reasoning'],
                'description' => 'Mistral AI - High-performance open source model',
            ],
            [
                'name' => 'mistral:7b',
                'provider' => 'local',
                'type' => 'text',
                'cost_per_token' => 0.0,
                'cost_per_image' => null,
                'is_active' => true, // Phase 3 - Now active
                'capabilities' => ['text_generation', 'chat'],
                'description' => 'Mistral AI 7B - Smaller, faster version',
            ],
        ];

        foreach ($models as $modelData) {
            AiModel::updateOrCreate(
                ['name' => $modelData['name']],
                $modelData
            );
        }
    }
}
