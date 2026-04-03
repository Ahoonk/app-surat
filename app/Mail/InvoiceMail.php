<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $pdfContent,
        public string $fileName
    ) {}

    public function build()
    {
        return $this->subject('Invoice ' . $this->invoice->nomor)
            ->view('emails.invoice')
            ->with(['invoice' => $this->invoice])
            ->attachData($this->pdfContent, $this->fileName, ['mime' => 'application/pdf']);
    }
}
