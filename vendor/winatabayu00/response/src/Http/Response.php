<?php

namespace Winata\Core\Response\Http;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Winata\Core\Response\Contracts\OnResponse;
use Winata\Core\Response\Enums\DefaultResponseCode;

class Response implements Responsable
{
    /**
     * Response constructor.
     *
     * @param OnResponse $code
     * @param Arrayable|array<int|string, mixed>|null $data
     * @param string|null $message
     */
    public function __construct(
        protected OnResponse                                                                                                       $code = DefaultResponseCode::SUCCESS,
        protected JsonResource|ResourceCollection|Arrayable|LengthAwarePaginator|\Illuminate\Pagination\CursorPaginator|array|null $data = null,
        protected ?string                                                                                                          $message = null,
    )
    {
    }

    /**
     * Get response data.
     *
     * @return array<int|string, mixed>|null
     */
    public function getData(): ?array
    {
        return $this->data instanceof Arrayable ? $this->data->toArray() : $this->data;
    }

    /**
     * Get response message.
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message ?? $this->code->message();
    }

    /**
     * Get response data.
     *
     * @return array<string, mixed>
     */
    public function getResponseData(): array
    {
        $resp = [
            'rc' => $this->code->name,
            'message' => $this->getMessage(),
            'timestamp' => now(),
        ];


        if ($this->data instanceof Paginator) {
            return array_merge($resp, ['payload' => $this->data->toArray()]);
        }

        if ($this->data instanceof Arrayable) {
            return array_merge($resp, ['payload' => [JsonResource::$wrap => $this->data->toArray()]]);
        }

        if (($this->data?->resource ?? null) instanceof AbstractPaginator) {
            return array_merge($resp, [
                'payload' => array_merge(
                    $this->data->resource->toArray(),
                    [JsonResource::$wrap => $this->getData()]
                )
            ]);
        }

        return array_merge($resp, [
            'payload' => is_null($this->data) ? $this->data : [JsonResource::$wrap => $this->data]
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \JsonException
     */
    public function toResponse($request): \Illuminate\Http\Response|JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($request->expectsJson()) {
            return response()->json($this->getResponseData(), $this->code->httpCode());
        }

        return new \Illuminate\Http\Response(json_encode($this->getResponseData(), JSON_THROW_ON_ERROR), $this->code->httpCode());
    }
}
