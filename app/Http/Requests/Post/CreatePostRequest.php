<?php

namespace App\Http\Requests\Post;

use App\Enums\PostType;
use App\Models\InstagramAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow authenticated users; fine-grained checks are enforced via business rules
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
            // Only caption is required
            'caption' => 'required|string|max:2200',

            // All other fields optional for now
            'instagram_account_id' => [
                'nullable',
                'integer',
                'exists:instagram_accounts,id',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return; // optional
                    }
                    $account = InstagramAccount::find($value);
                    $user = $this->user();
                    if ($account && $user && $user->currentCompany) {
                        if ((int) $account->company_id !== (int) $user->currentCompany->id) {
                            $fail(__('posts.instagram_account_access_denied'));
                        }
                    }
                },
            ],
            'type' => [
                'nullable',
                'string',
                Rule::enum(PostType::class),
            ],
            'title' => 'nullable|string|max:255',
            'scheduled_at' => 'nullable|date|after:now',
            'media' => 'nullable|array',
            'media.*.type' => 'required_with:media|string|in:image,video',
            'media.*.file' => 'required_with:media|file|mimes:jpeg,png,gif,webp,mp4,mov,avi|max:102400', // 100MB max
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
            'instagram_account_id.required' => __('posts.instagram_account_required'),
            'instagram_account_id.exists' => __('posts.instagram_account_not_found'),
            'type.required' => __('posts.type_required'),
            'type.enum' => __('posts.invalid_type'),
            'caption.max' => __('posts.caption_too_long'),
            'scheduled_at.after' => __('posts.scheduled_time_must_be_future'),
            'media.required' => __('posts.media_required'),
            'media.min' => __('posts.at_least_one_media'),
            'media.*.type.required' => __('posts.media_type_required'),
            'media.*.type.in' => __('posts.invalid_media_type'),
            'media.*.file.required' => __('posts.file_required'),
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
            // Use plain strings to avoid arrays from translation groups causing formatter errors
            'instagram_account_id' => 'instagram account',
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
            $this->validateMediaCount($validator);
            $this->validateMediaTypes($validator);
        });
    }

    /**
     * Validate media count based on post type
     */
    private function validateMediaCount($validator): void
    {
        $typeInput = $this->input('type');
        if (! $typeInput) {
            return; // Skip validation if no type provided
        }

        $type = PostType::from($typeInput);
        $mediaCount = count($this->input('media', []));

        if ($mediaCount > $type->maxMediaCount()) {
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
        $typeInput = $this->input('type');
        if (! $typeInput) {
            return; // Skip validation if no type provided
        }

        $type = PostType::from($typeInput);
        $allowedTypes = $type->allowedMediaTypes();
        $media = $this->input('media', []);

        foreach ($media as $index => $mediaItem) {
            if (isset($mediaItem['type']) && ! in_array($mediaItem['type'], $allowedTypes)) {
                $validator->errors()->add("media.{$index}.type", __('posts.invalid_media_type_for_post', [
                    'media_type' => $mediaItem['type'],
                    'post_type' => $type->label(),
                ]));
            }
        }
    }
}
