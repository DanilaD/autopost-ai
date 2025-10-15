<?php

namespace App\Http\Requests\Post;

use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Temporarily allow all authenticated users to update posts
        // TODO: Implement proper Post policy when needed
        return (bool) $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Only caption required when provided; updates can omit fields
            'type' => [
                'sometimes',
                'nullable',
                'string',
                Rule::enum(PostType::class),
            ],
            'title' => 'sometimes|nullable|string|max:255',
            'caption' => 'sometimes|nullable|string|max:2200',
            'scheduled_at' => 'sometimes|nullable|date|after:now',
            'media' => 'sometimes|nullable|array',
            'media.*.type' => 'required_with:media|string|in:image,video',
            'media.*.file' => 'required_with:media|file|mimes:jpeg,png,gif,webp,mp4,mov,avi|max:102400',
            'media.*.order' => 'integer|min:0',
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
            'type.enum' => __('posts.invalid_type'),
            'caption.max' => __('posts.caption_too_long'),
            'scheduled_at.after' => __('posts.scheduled_time_must_be_future'),
            'media.min' => __('posts.at_least_one_media'),
            'media.*.type.required_with' => __('posts.media_type_required'),
            'media.*.type.in' => __('posts.invalid_media_type'),
            'media.*.file.required_with' => __('posts.file_required'),
            'media.*.file.mimes' => __('posts.invalid_file_type'),
            'media.*.file.max' => __('posts.file_too_large'),
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
            // Use plain strings to avoid translation arrays interfering with formatting
            'type' => 'type',
            'title' => 'title',
            'caption' => 'caption',
            'scheduled_at' => 'scheduled at',
            'media' => 'media',
            'media.*.type' => 'media type',
            'media.*.file' => 'media file',
            'media.*.order' => 'media order',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validatePostCanBeEdited($validator);
            $this->validateMediaCount($validator);
            $this->validateMediaTypes($validator);
        });
    }

    /**
     * Validate that post can be edited
     */
    private function validatePostCanBeEdited($validator): void
    {
        $post = $this->route('post');

        if (! $post->canBeEdited()) {
            $validator->errors()->add('post', __('posts.cannot_edit_published'));
        }
    }

    /**
     * Validate media count based on post type
     */
    private function validateMediaCount($validator): void
    {
        $type = $this->input('type') ? PostType::from($this->input('type')) : $this->route('post')->type;
        $mediaCount = count($this->input('media', []));

        if ($mediaCount > 0 && $mediaCount > $type->maxMediaCount()) {
            $validator->errors()->add('media', __('posts.too_many_media', [
                'max' => $type->maxMediaCount(),
                'type' => $type->label(),
            ]));
        }
    }

    /**
     * Validate media types based on post type
     */
    private function validateMediaTypes($validator): void
    {
        $type = $this->input('type') ? PostType::from($this->input('type')) : $this->route('post')->type;
        $allowedTypes = $type->allowedMediaTypes();
        $media = $this->input('media', []);

        foreach ($media as $index => $mediaItem) {
            if (! in_array($mediaItem['type'], $allowedTypes)) {
                $validator->errors()->add("media.{$index}.type", __('posts.invalid_media_type_for_post', [
                    'media_type' => $mediaItem['type'],
                    'post_type' => $type->label(),
                ]));
            }
        }
    }
}
