<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    use Queueable;

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $appName = config('app.name');
        $url = $this->resetUrl($notifiable);

        // Use a custom view based on our unified email template
        return (new MailMessage)
            ->subject(__('Reset Password Notification').' - '.$appName)
            ->view('emails.auth.reset-password', [
                'appName' => $appName,
                'resetUrl' => $url,
                'notifiable' => $notifiable,
            ]);
    }
}
