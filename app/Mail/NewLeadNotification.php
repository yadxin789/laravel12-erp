<?php

namespace App\Mail;

use App\Models\Crm\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLeadNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "新销售线索：{$this->lead->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-lead',
            with: [
                'leadName' => $this->lead->name,
                'contactPerson' => $this->lead->contact_person,
                'phone' => $this->lead->phone,
                'source' => $this->lead->source,
            ],
        );
    }
}
