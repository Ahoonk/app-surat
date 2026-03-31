<?php

namespace App\Mail;

use App\Models\NotaToko;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotaTokoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NotaToko $notaToko,
        public string $pdfContent,
        public string $fileName
    ) {}

    public function build()
    {
        return $this->subject('Nota Toko ' . $this->notaToko->nomor)
            ->view('emails.nota-toko')
            ->with(['notaToko' => $this->notaToko])
            ->attachData($this->pdfContent, $this->fileName, ['mime' => 'application/pdf']);
    }
}
