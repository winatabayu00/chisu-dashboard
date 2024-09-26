<?php

namespace Winata\Core\Response\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Winata\Core\Telegram\Concerns\Messages\Message;

class SendingTelegramNotification implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $performedBy,
        private array|Arrayable $carrier,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $parseEvent = [
            'performerBy' => $this->performedBy,
            'carrier' => $this->carrier,
        ];
        $this->sendNotify(event: array_to_object($parseEvent));
    }

    /**
     * @param object $event
     * @return void
     */
    public function sendNotify(object $event): void
    {
        $sendToTelegram = sendToTelegram(
            token: config('winata.response.driver.telegram.token'),
            chatId: config('winata.response.driver.telegram.chat_id')
        )
            ->setTitle(title: config('winata.response.driver.telegram.formatting.title'), callable: function (Message $message) {
                return $message
                    ->setMessage(message: "FROM APP : " . config('winata.response.app_name'));
            });

        /* begin: cc */
        $sendToTelegram
            ->setCc(callable: function (Message $message) use ($event) {

                $performer = $event->performerBy;
                if ($user = auth()->user()) {
                    $who = config('winata.response.performer.performer_column');
                    $performer = $user->$who;
                }

                return $message
                    ->setMessage("performerBy: {$performer}")
                    ->setMessage(config('winata.response.driver.telegram.formatting.cc'));
            });
        /* end: cc */

        $data = array_to_object($event->carrier);

        if (isset($data->rc)) {
            $sendToTelegram
                ->setMessage(message: "RC : {$data->rc}");
        }

        $now = now()->toDateTimeString();
        $sendToTelegram
            ->setMessage(message: "MESSAGE : {$data->message}")
            ->setMessage(message: "TIME : {$now}")
            ->setMessage(message: "CODE : {$data->code}")
            ->setMessage(message: "URL : {$data->url}")
            ->setMessage(message: "IP : {$data->ip}");
        if (isset($data->data)) {
            $sendToTelegram
                ->setMessage(message: "DATA : " . json_encode($data->data));
        }

        /* begin:: additional */
        $sendToTelegram
            ->setMessage(callable: function (Message $message) use ($data) {
                return $message
                    ->setMessage(message: '')
                    ->setMessage(message: '----ADDITIONAL----', format: '*')
                    ->setMessage(message: "SOURCE : {$data->source}")
                    ->setMessage(message: "FILE : {$data->file}")
                    ->setMessage(message: "LINE : {$data->line}");
            });
        /* end:: additional */

        $sendToTelegram
            ->sendMessage();
    }
}
