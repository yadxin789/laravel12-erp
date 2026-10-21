<?php

namespace App\Mail;

use App\Models\Sales\SalesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SalesOrderSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SalesOrder $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "销售订单待审批：{$this->order->order_no}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.sales-order-submitted',
            with: [
                'orderNo' => $this->order->order_no,
                'customerName' => $this->order->customer?->name,
                'totalAmount' => $this->order->total_amount,
            ],
        );
    }
}
