<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderBillMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {
    }

    public function build()
    {
        return $this
            ->subject(
                'Order Bill #' . $this->order->id
            )
            ->view('emails.order-bill');
    }
}
