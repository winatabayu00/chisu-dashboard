<?php

namespace Winata\Core\Response\Exception;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Winata\Core\Response\Concerns\CatchableError;
use Winata\Core\Response\Events\OnErrorEvent;

class ReportableException extends ExceptionHandler
{

    use CatchableError;

    /**
     * @param \Throwable $e
     * @return void
     * @throws \Throwable
     */
    public function report(\Throwable $e): void
    {
        $this->logException($e);

        parent::report($e);
    }

    /**
     * @param \Throwable $exception
     * @return void
     */
    private function logException(\Throwable $exception): void
    {
        $data = [
            'url' => request()->url() ?? null,
            'ip' => request()->ip(),
            'rc' => null,
            'data' => null,
            'source' => $exception::class,
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'timestamp' => now(),
            'trace' => $exception->getTrace(),
            // Include any other additional information you want to store
        ];

        if (isset($exception->rc)) {
            $data['rc'] = $exception->rc->name;
            if (empty($data['message'])){
                $data['message'] = $exception->rc->message();
            }
        }

        if (isset($exception->data)) {
            $data['data'] = $exception->data;
        }

        event(new OnErrorEvent(carrier: $data));
    }
}
