<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertAplikasi extends Notification
{
    public function __construct(
        public readonly string $judul,
        public readonly string $detail,
        public readonly bool $pulih = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(($this->pulih ? '[PULIH] ' : '[ALERT] ').$this->judul.' · '.config('app.name'))
            ->line($this->detail)
            ->line('Waktu: '.now()->translatedFormat('d M Y H:i:s').' WIB');

        return $this->pulih
            ? $mail->line('Aplikasi kembali normal.')
            : $mail->error()->action('Buka halaman Monitoring', route('monitoring'));
    }
}
