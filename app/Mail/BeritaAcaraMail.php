<?php

namespace App\Mail;

use App\Models\BeritaAcara;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BeritaAcaraMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BeritaAcara $beritaAcara,
        public string $pdfContent,
        public string $fileName
    ) {}

    public function build()
    {
        return $this->subject('Berita Acara ' . $this->beritaAcara->nomor)
            ->view('emails.berita-acara')
            ->with(['beritaAcara' => $this->beritaAcara])
            ->attachData($this->pdfContent, $this->fileName, ['mime' => 'application/pdf']);
    }
}
