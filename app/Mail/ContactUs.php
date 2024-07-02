<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactUs extends Mailable
{
    use Queueable, SerializesModels;

    public $contactData;

    /**
     * Create a new message instance.
     *
     * @param array $contactData
     */
    public function __construct(array $contactData)
    {
        $this->contactData = $contactData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->subject('Hola, has realizado: ' . $this->contactData['subject'])
                      ->markdown('mail.contact-us', ['contactData' => $this->contactData]);

        if (isset($this->contactData['files'])) {
            foreach ($this->contactData['files'] as $file) {
                $email->attach($file['path'], [
                    'as' => $file['name'],
                    'mime' => $file['mime'],
                ]);
            }
        }

        return $email;
    }
}
