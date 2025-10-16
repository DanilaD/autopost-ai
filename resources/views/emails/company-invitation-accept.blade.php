<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('emails.company_invitation.accept_subject', ['company_name' => $company->name, 'app_name' => config('app.name')]) }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .content {
            padding: 40px 30px;
        }
        .company-info {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .company-name {
            font-size: 20px;
            font-weight: 600;
            color: #14532d;
            margin: 0 0 10px 0;
        }
        .role-badge {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            text-transform: capitalize;
        }
        .cta-button {
            display: inline-block;
            background-color: #10b981;
            color: white;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            transition: background-color 0.2s;
        }
        .cta-button:hover {
            background-color: #059669;
        }
        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .expiry-notice {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #92400e;
        }
        .inviter-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            border-radius: 0 6px 6px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ __('emails.company_invitation.accept_welcome', ['app_name' => config('app.name')]) }}</h1>
            <p>{{ __('emails.company_invitation.accept_subtitle') }}</p>
        </div>

        <div class="content">
            <h2>{{ __('emails.company_invitation.accept_greeting', ['name' => $invitedUser->name ?? $invitation->email]) }}</h2>
            
            <p>{{ __('emails.company_invitation.accept_intro') }}</p>

            <div class="company-info">
                <div class="company-name">{{ $company->name }}</div>
                <p>{{ __('emails.company_invitation.invited_by') }}: <strong>{{ $inviter->name }}</strong></p>
                <p>{{ __('emails.company_invitation.your_role') }}: <span class="role-badge">{{ __('emails.company_invitation.role_' . $invitation->role) }}</span></p>
            </div>

            <div class="inviter-info">
                <p><strong>{{ __('emails.company_invitation.personal_message') }}</strong></p>
                <p>{{ __('emails.company_invitation.accept_message', ['company_name' => $company->name, 'inviter_name' => $inviter->name]) }}</p>
            </div>

            <div style="text-align: center;">
                <a href="{{ $acceptUrl }}" class="cta-button">
                    {{ __('emails.company_invitation.accept_button') }}
                </a>
            </div>

            <div class="expiry-notice">
                <strong>{{ __('emails.company_invitation.expiry_title') }}</strong><br>
                {{ __('emails.company_invitation.expiry_message', ['expires_at' => $expiresAt->format('M j, Y \a\t g:i A')]) }}
            </div>

            <p>{{ __('emails.company_invitation.accept_footer') }}</p>
        </div>

        <div class="footer">
            <p>{{ __('emails.company_invitation.footer_text', ['app_name' => config('app.name')]) }}</p>
            <p>{{ __('emails.company_invitation.footer_copyright', ['year' => date('Y'), 'app_name' => config('app.name')]) }}</p>
        </div>
    </div>
</body>
</html>

