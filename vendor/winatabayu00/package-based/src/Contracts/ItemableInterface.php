<?php

namespace Winata\PackageBased\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface ItemableInterface
{
    public function invoiceDetails(): MorphTo;
}
