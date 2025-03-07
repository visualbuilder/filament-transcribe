<?php

namespace Visualbuilder\FilamentTranscribe\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Visualbuilder\FilamentTranscribe\Models\Transcript;

class TranscriptUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transcript;

    /**
     * Create a new event instance.
     */
    public function __construct(Transcript $transcript)
    {
        $this->transcript = $transcript;
    }

    /**
     * The channel the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        // You can use a private channel if you only want authorized users to listen
        // Make sure to define authorization in routes/channels.php
        return new PrivateChannel('transcript.'.$this->transcript->id);
    }

    /**
     * The event name that the client will listen for. (Optional override)
     */
    public function broadcastAs(): string
    {
        return 'transcript.updated';
    }
}
