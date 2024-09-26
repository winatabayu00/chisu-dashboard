<?php

namespace Winata\Core\Response\Listeners\OnErrorEvent;

use Winata\Core\Response\Jobs\SendingTelegramNotification;
use Winata\Core\Telegram\Concerns\Messages\Message;

class SendToTelegram
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $isLogging = config('winata.response.reportable.telegram.logging');

        if ($isLogging)
        {
            $carrier = $event->carrier;
            $carrier['trace'] = null;
            dispatch(new SendingTelegramNotification(
                performedBy: $event->performerBy,
                carrier: $carrier,
            ))
                ->onQueue('error_log');
        }
    }
}
