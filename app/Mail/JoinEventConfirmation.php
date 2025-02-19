<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class JoinEventConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public Event $event,
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Event ' . $this->event->name . ' Joined Successfully',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.join-event-confirmation',
            with: ['event_address' => $this->getAddressFromLatLongOSM(
                $this->event->location->latitude,
                $this->event->location->longitude,
            )],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
    function getAddressFromLatLongOSM($latitude, $longitude)
    {
        # ToDo : fix ssl issue
        $response = Http::withoutVerifying()->get("https://nominatim.openstreetmap.org/reverse", [
            'lat' => $latitude,
            'lon' => $longitude,
            'format' => 'json'
        ]);

        $data = $response->json();

        if (!empty($data['display_name'])) {
            return $data['display_name']; // Return the found address
        }

        // If address lookup fails, return a clickable Google Maps link
        return '<a href="https://www.google.com/maps?q=' . $latitude . ',' . $longitude . '" target="_blank">Open in Google Maps</a>';
    }
}
