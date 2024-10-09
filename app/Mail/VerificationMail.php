<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $verificationUrl;
    public $name;

    /**
     * Create a message instance
     * 
     * @param string $name
     * @param string $verificationUrl
     * @return void
     */

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $this->name = $name;
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verification Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.verification',
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


    /**
     * Build the message
     * 
     * @return $this
     */

     public function build()
     {
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                    ->subject('Verifikasi Alamat Email Anda')
                    ->html($this->buildEmailContent());
     }

     protected function buildEmailContent()
     {
        return "
            <h1>Halo, {$this->name}</h1>
            <p>Terima kasih telah mendaftar. Silakan klik link di bawah ini untuk memverifikasi alamat email Anda:</p>
            <a href=\"{$this->verificationUrl}\">Verifikasi Email</a>
            <p>Jika Anda tidak melakukan pendaftaran, silakan abaikan email ini.</p>
        ";
     }
}
