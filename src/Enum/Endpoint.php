<?php

declare(strict_types=1);

namespace Camoo\Payment\Enum;

enum Endpoint: string
{
    case ACCOUNT = '/account';
    case CASH_OUT = '/cashout';
    case VERIFY = '/verify';
}
