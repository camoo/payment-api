<?php

declare(strict_types=1);

namespace Camoo\Payment\Enum;

enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';
    case XAF = 'XAF';
    case XOF = 'XOF';
}
