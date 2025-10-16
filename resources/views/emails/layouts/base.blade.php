<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('email_subject', config('app.name'))</title>
    <style>
        /* Base */
        body { margin:0; padding:0; background:#f3f4f6; color:#111827; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; line-height:1.6; }
        a { color:#111827; text-decoration:none; }
        .container { max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; }
        /* Angular design (no rounded corners) */
        .header, .content, .footer, .banner, .btn { border-radius:0 !important; }
        /* Header */
        .header { background:#111827; color:#ffffff; padding:28px 32px; border-bottom:1px solid #1f2937; }
        .header h1 { margin:0; font-size:20px; font-weight:600; letter-spacing:0.2px; }
        .header p { margin:6px 0 0 0; color:#d1d5db; font-size:13px; }
        /* Content */
        .content { padding:32px; }
        .title { margin:0 0 4px 0; font-size:18px; font-weight:700; }
        .subtitle { margin:0 0 18px 0; font-size:14px; color:#6b7280; }
        .block { margin:18px 0; padding:16px; border:1px solid #e5e7eb; background:#fafafa; }
        .muted { color:#6b7280; font-size:13px; }
        /* Button */
        .btn { display:inline-block; background:#111827; color:#ffffff; padding:12px 20px; border:1px solid #111827; font-weight:600; font-size:14px; letter-spacing:0.2px; }
        .btn:hover { background:#000000; border-color:#000000; }
        /* Footer */
        .footer { background:#f9fafb; color:#6b7280; padding:24px 32px; border-top:1px solid #e5e7eb; font-size:12px; text-align:center; }
    </style>
    @yield('head')
    @stack('styles')
  </head>
  <body>
    <div class="container">
        <div class="header">
            <h1>@yield('header_title', config('app.name'))</h1>
            @hasSection('header_subtitle')
                <p>@yield('header_subtitle')</p>
            @endif
        </div>

        <div class="content">
            @hasSection('email_title')
                <h2 class="title">@yield('email_title')</h2>
            @endif
            @hasSection('email_subtitle')
                <p class="subtitle">@yield('email_subtitle')</p>
            @endif

            @yield('email_content')
        </div>

        <div class="footer">
            <p>{{ __('emails.company_invitation.footer_text', ['app_name' => config('app.name')]) }}</p>
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
  </body>
</html>


