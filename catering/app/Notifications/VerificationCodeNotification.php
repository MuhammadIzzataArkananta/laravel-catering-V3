<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationCodeNotification extends Notification
{
    use Queueable;

    protected $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Kode Verifikasi Akun Anda')
            ->line('Gunakan kode verifikasi berikut untuk mengaktifkan akun Anda:')
            ->line($this->code)
            ->line('Jika Anda tidak membuat akun, abaikan email ini.');
    }
}
