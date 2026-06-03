<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPassword extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        // Pastikan URL dikodekan dengan benar dan membawa email serta token
        $url = rtrim($frontendUrl, '/') . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Pemberitahuan Reset Password')
            ->greeting('Halo!')
            ->line('Anda menerima email ini karena kami menerima permintaan pengaturan ulang kata sandi untuk akun Anda.')
            ->action('Reset Password', $url)
            ->line('Tautan reset password ini akan kedaluwarsa dalam 60 menit.')
            ->line('Jika Anda tidak meminta pengaturan ulang kata sandi, abaikan email ini.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
