<?php

namespace App\Http\Controllers;

use App\Enums\Service;
use App\Enums\Target;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use \Winata\Core\Response\Controllers\Api\Controller as BaseController;

abstract class Controller extends BaseController
{
    protected array $responseMessages;

    protected function parseResponse(Target $target, Service $indicator, Arrayable|Collection $data)
    {
        return [
            'target' => $target->value,
            'indicator' => $indicator->value,
            'result' => $data,
        ];
    }
}

