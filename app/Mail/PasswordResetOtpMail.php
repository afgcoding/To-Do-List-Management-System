<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordResetOtpMail extends Mailable
{
    public function __construct(
        public string $code,
        public string $recipientName,
        public string $companyName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your {$this->companyName} password reset code",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.password-reset-otp',
        );
    }
}
