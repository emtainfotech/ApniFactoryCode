<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgentAttributionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $headline,
        public string $bodyMessage,
        public array $details = [],
        public ?string $actionUrl = null,
        public string $actionLabel = 'View in Portal'
    ) {
    }

    public function build()
    {
        return $this->subject($this->headline)
            ->view('emails.agent_attribution')
            ->with([
                'name'        => $this->recipientName,
                'subject'     => $this->headline,
                'headline'    => $this->headline,
                'bodyMessage' => $this->bodyMessage,
                'details'     => $this->details,
                'actionUrl'   => $this->actionUrl,
                'actionLabel' => $this->actionLabel,
            ]);
    }
}

