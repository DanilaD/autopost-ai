<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public CompanyInvitation $invitation;

    public Company $company;

    public User $inviter;

    public ?User $invitedUser;

    public string $invitationType;

    /**
     * Create a new message instance.
     */
    public function __construct(CompanyInvitation $invitation, string $invitationType = 'accept')
    {
        $this->invitation = $invitation;
        $this->company = $invitation->company;
        $this->inviter = $invitation->inviter;
        $this->invitedUser = User::where('email', $invitation->email)->first();
        $this->invitationType = $invitationType; // 'accept' or 'register'
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName = config('app.name', 'Laravel');
        $subject = __('emails.company_invitation.accept_subject', ['company_name' => $this->company->name, 'app_name' => $appName]);

        return new Envelope(subject: $subject);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.company-invitation-accept',
            with: [
                'invitation' => $this->invitation,
                'company' => $this->company,
                'inviter' => $this->inviter,
                'invitedUser' => $this->invitedUser,
                'invitationType' => 'accept',
                'acceptUrl' => route('invitations.show', $this->invitation->token),
                'expiresAt' => $this->invitation->expires_at,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
