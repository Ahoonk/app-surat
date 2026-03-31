<?php

namespace App\Mail;

use App\Models\SuratJalan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SuratJalanMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SuratJalan $suratJalan,
        public string $pdfContent,
        public string $fileName
    ) {}

    public function build()
    {
        return $this->subject('Surat Jalan ' . $this->suratJalan->nomor)
            ->view('emails.surat-jalan')
            ->with(['suratJalan' => $this->suratJalan])
            ->attachData($this->pdfContent, $this->fileName, ['mime' => 'application/pdf']);
    }
}
