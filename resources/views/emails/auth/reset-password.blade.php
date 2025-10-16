@extends('emails.layouts.base')

@section('email_subject', __('Reset Password Notification'))
@section('header_title', config('app.name'))
@section('header_subtitle', __('Security notification'))

@section('email_title', __('Reset your password'))
@section('email_subtitle', __('You requested a password reset.'))

@section('email_content')
<p>{{ __('Click the button below to reset your password:') }}</p>

<p style="margin: 18px 0;">
    <a href="{{ $resetUrl }}" class="btn">{{ __('Reset Password') }}</a>
    <br>
    <span class="muted">{{ __('This link will expire in 60 minutes.') }}</span>
  </p>

<div class="block">
    <p class="muted">{{ __('If you did not request a password reset, no further action is required.') }}</p>
    <p class="muted">{{ __('If the button does not work, copy and paste this URL:') }}</p>
    <p class="muted"><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
</div>
@endsection


