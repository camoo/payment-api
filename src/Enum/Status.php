<?php

declare(strict_types=1);

namespace Camoo\Payment\Enum;

enum Status: string
{
    case CREATED = 'CREATED';
    case INITIALISED = 'INITIALISED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case CONFIRMED = 'CONFIRMED';
    case FAILED = 'FAILED';
    case CANCELED = 'CANCELED';
}
