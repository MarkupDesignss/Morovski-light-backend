<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
// use Barryvdh\DomPDF\Facade\Pdf;  // COMMENT THIS

class ProformaInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    // public $pdf;  // COMMENT

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        // $this->pdf = Pdf::loadView('pdf.proforma_invoice', ['invoice' => $invoice]); // COMMENT
    }

    public function build()
    {
        $mail = $this->subject('Proforma Invoice #' . $this->invoice->invoice_number)
                    ->view('emails.proforma_invoice')
                    ->with(['invoice' => $this->invoice]);
        
        // Remove attachment for now
        // ->attachData($this->pdf->output(), 'Proforma_Invoice_' . $this->invoice->invoice_number . '.pdf', [
        //     'mime' => 'application/pdf',
        // ]);
        
        return $mail;
    }
}