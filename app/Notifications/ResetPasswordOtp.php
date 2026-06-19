<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordOtp extends Notification
{
    use Queueable;

    public function __construct(public readonly string $otp)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode OTP Reset Password')
            ->view('emails.reset-password-otp', [
                'appName' => config('app.name', 'Dental Health'),
                'email' => $notifiable->getEmailForPasswordReset(),
                'otp' => $this->otp,
                'expiresInMinutes' => 60,
            ]);
    }
}
