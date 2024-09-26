<?php

namespace Winata\Core\Response\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OnErrorEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $performerBy = 'system';
    /**
     * Create a new event instance.
     */
    public function __construct(
        public array|object $carrier,
    )
    {
    }
}
