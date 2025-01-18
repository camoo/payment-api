<?php

declare(strict_types=1);

namespace Camoo\Payment\Models;

use Camoo\Payment\Enum\Currency;
use Camoo\Payment\Exception\InvalidArgumentException;
use Camoo\Payment\ValueObject\Money;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Class Payment
 *
 * Represents a payment resource with various properties
 * including amount, network, status, timestamps, etc.
 */
final class Payment implements ModelInterface
{
    public function __construct(
        public readonly int $id,
        public readonly Money $amount,
        public readonly DateTimeInterface $createdAt,
        public readonly string $network,
        public readonly string $status,
        public readonly ?float $fees = null,
        public readonly ?float $netAmount = null,
        public readonly ?DateTimeInterface $completedAt = null,
        public readonly ?DateTimeInterface $notifiedAt = null,
        public readonly ?string $phoneNumber = null,
        public readonly ?string $country = null,
    ) {
    }

    /**
     * Create a Payment model from an associative array of data.
     *
     * Expected fields:
     * - id (int)
     * - amount (float)
     * - currency (string)
     * - createdAt (int timestamp OR date string)
     * - network (string)
     * - status (string)
     * Optional fields:
     * - fees (float)
     * - netAmount (float)
     * - completedAt (int timestamp OR date string)
     * - notifiedAt (int timestamp OR date string)
     * - phoneNumber (string)
     * - country (string)
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // Basic validation
        if (!isset($data['id'],
            $data['amount'],
            $data['currency'],
            $data['createdAt'],
            $data['network'],
            $data['status'])) {
            throw new InvalidArgumentException('Missing required fields for Payment creation.');
        }

        return new self(
            id: (int)$data['id'],
            amount: new Money(
                amount: (float)$data['amount'],
                currency: Currency::from($data['currency'])
            ),
            createdAt: self::parseDateTime($data['createdAt']),
            network: (string)$data['network'],
            status: (string)$data['status'],
            fees: isset($data['fees']) ? (float)$data['fees'] : null,
            netAmount: isset($data['netAmount']) ? (float)$data['netAmount'] : null,
            completedAt: isset($data['completedAt']) ? self::parseDateTime($data['completedAt']) : null,
            notifiedAt: isset($data['notifiedAt']) ? self::parseDateTime($data['notifiedAt']) : null,
            phoneNumber: $data['phoneNumber'] ?? null,
            country: $data['country'] ?? null
        );
    }

    /**
     * Convert this Payment model to an array representation.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount->toArray(),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'network' => $this->network,
            'status' => $this->status,
            'fees' => $this->fees,
            'netAmount' => $this->netAmount,
            'completedAt' => $this->completedAt?->format('Y-m-d H:i:s'),
            'notifiedAt' => $this->notifiedAt?->format('Y-m-d H:i:s'),
            'phoneNumber' => $this->phoneNumber,
            'country' => $this->country,
        ];
    }

    /** Helper method to parse date/time from either timestamp (int) or string. */
    private static function parseDateTime(int|string $value): DateTimeInterface
    {
        // If numeric, treat as a UNIX timestamp.
        if (is_numeric($value)) {
            return (new DateTimeImmutable())->setTimestamp((int)$value);
        }

        // Otherwise, treat it as a date/time string.
        return new DateTimeImmutable((string)$value);
    }
}
