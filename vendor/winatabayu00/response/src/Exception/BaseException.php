<?php

namespace Winata\Core\Response\Exception;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Throwable;
use Winata\Core\Response\Contracts\OnResponse;
use Winata\Core\Response\Enums\DefaultResponseCode;

class BaseException extends Exception implements Arrayable
{

    /**
     * Base BaseException constructor.
     *
     * @param OnResponse $rc
     * @param ?string $message
     * @param array|null $data
     * @param Throwable|null $previous
     */
    public function __construct(
        public OnResponse       $rc = DefaultResponseCode::ERR_UNKNOWN,
        ?string              $message = null,
        public array|null $data = null,
        ?Throwable           $previous = null
    )
    {
        $code = $rc->httpCode() ?? 0;
        if (is_null($message)){
            $message = $this->rc->message();
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get response code.
     *
     * @return string
     */
    public function getResponseCode(): string
    {
        return $this->rc->name;
    }

    /**
     * Get response message.
     *
     * @return string
     */
    public function getResponseMessage(): string
    {
        if (config('app.debug') && $this->getPrevious() instanceof Throwable) {
            return $this->getPrevious()->getMessage();
        }

        return $this->message;
    }

    /**
     * Get error data.
     *
     * @return array|null
     */
    public function getErrorData(): ?array
    {
        return $this->data;
    }

    /** {@inheritDoc} */
    public function toArray(): array
    {
        $carrier = [
            'rc' => $this->getResponseCode(),
            'message' => $this->getResponseMessage(),
            'timestamp' => now(),
            'payload' => $this->getErrorData(),
        ];

        if (config('app.debug') && $this->getPrevious() instanceof Throwable) {
            $carrier['debug'] = [
                'class' => get_class($this->getPrevious()),
                'file' => $this->getPrevious()->getFile(),
                'line' => $this->getPrevious()->getLine(),
                'trace' => $this->getPrevious()->getTrace(),
            ];
        }

        return $carrier;
    }
}
