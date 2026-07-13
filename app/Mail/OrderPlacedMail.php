<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $type;
    public $invoice;

    public function __construct($order, $type, $invoice = null)
    {
        $this->order = $order;
        $this->type = $type;
        $this->invoice = $invoice;
    }

   public function build()
{
    /*
    --------------------------------
    SUBJECT DYNAMIC
    --------------------------------
    */
    switch ($this->type) {
        case 'proforma':
            $subject = 'Proforma Invoice - Invoice #' . $this->invoice->id;
            break;

        case 'dispatch':
            $subject = 'Items Shipped - Invoice #' . $this->invoice->id;
            break;

        case 'invoice':
            $subject = 'Payment Completed - Invoice #' . $this->order->order_number;
            break;

        case 'cash_invoice':
            $subject = 'Cash Invoice - Invoice #' . $this->invoice->id;
            break;

        default:
            $subject = 'Payment Received - Invoice #' . $this->invoice->id;
            break;
    }

    /*
    --------------------------------
    USER HANDLE
    --------------------------------
    */
    $user = ($this->type == 'invoice')
        ? (object)[
            'full_name' => $this->order->user->full_name
        ]
        : $this->invoice->client;

    return $this->subject($subject)
        ->view('emails.order-confirmed')
        ->with([
            'user'    => $user,
            'type'    => $this->type,
            'invoice' => $this->invoice
        ]);
}
}