<?php

namespace Winata\Core\Response\Controllers\Api;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Winata\Core\Response\Contracts\OnResponse;
use Winata\Core\Response\Enums\DefaultResponseCode;
use Winata\Core\Response\Http\Response;

class Controller
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * @param JsonResource|ResourceCollection|Arrayable|LengthAwarePaginator|CursorPaginator|array<int|string, mixed>|null $data
     * @param DefaultResponseCode $rc
     * @param string|null $message
     * @return Response
     */
    public function response(
        JsonResource|ResourceCollection|Arrayable|LengthAwarePaginator|CursorPaginator|array|null $data = null,
        OnResponse                                                   $rc = DefaultResponseCode::SUCCESS,
        string                                                    $message = null,
    ): Response
    {
        return new Response($rc, $data, $message);
    }

    /**
     * Use to get response message
     *
     * @param string $context
     * @return string
     */
    public function getResponseMessage(string $context): string
    {
        return $this->responseMessages[$context];
    }
}
