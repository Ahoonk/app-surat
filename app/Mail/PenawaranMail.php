<?php

namespace App\Mail;

use App\Models\Penawaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PenawaranMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Penawaran $penawaran,
        public string $pdfContent,
        public string $fileName
    ) {}

    public function build()
    {
        return $this->subject('Surat Penawaran ' . $this->penawaran->nomor)
            ->view('emails.penawaran')
            ->with(['penawaran' => $this->penawaran])
            ->attachData($this->pdfContent, $this->fileName, ['mime' => 'application/pdf']);
    }
}
