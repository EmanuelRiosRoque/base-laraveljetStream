<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendFormat extends Notification
{
    use Queueable;

    private $documentos;
    private $subject;
    private $message;

    public function __construct($documentos, $subject, $message)
    {
        $this->documentos = $documentos;
        $this->subject = $subject;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject($this->subject)
            ->line($this->message);

        // Adjuntar cada archivo
        foreach ($this->documentos as $documento) {
            $mailMessage->attach($documento['path'], [
                'as' => $documento['name'],
                'mime' => $documento['extension'] == 'pdf' ? 'application/pdf' : 'application/octet-stream',
            ]);
        }

        return $mailMessage;
    }
}
