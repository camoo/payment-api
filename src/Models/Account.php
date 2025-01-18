<?php

declare(strict_types=1);

namespace Camoo\Payment\Models;

use Camoo\Payment\Enum\Currency;
use Camoo\Payment\ValueObject\Money;
use DateTimeImmutable;
use DateTimeInterface;

final class Account implements ModelInterface
{
    public function __construct(
        public readonly Money $balance,
        public readonly DateTimeInterface $viewedAt
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            new Money(
                amount: $data['account']['amount'],
                currency: Currency::from($data['account']['currency'])
            ),
            new DateTimeImmutable($data['account']['date'])
        );
    }

    public function toArray(): array
    {
        return [
            'balance' => [
                'amount' => $this->balance->amount,
                'currency' => $this->balance->currency->value,
            ],
            'viewedAt' => $this->viewedAt->format('Y-m-d H:i:s'),
        ];
    }
}
