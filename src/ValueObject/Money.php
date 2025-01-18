<?php

declare(strict_types=1);

namespace Camoo\Payment\ValueObject;

use Camoo\Payment\Enum\Currency;

final class Money
{
    public function __construct(
        public readonly float $amount,
        public readonly Currency $currency
    ) {
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency->value,
        ];
    }
}
