<?php

namespace App\Http\Controllers;

use \Winata\Core\Response\Controllers\Api\Controller as BaseController;

abstract class Controller extends BaseController
{
    protected array $responseMessages;
}

