<?php

declare(strict_types=1);

namespace Camoo\Payment\Models;

use Camoo\Payment\Enum\Currency;
use Camoo\Payment\Exception\InvalidArgumentException;
use Camoo\Payment\ValueObject\Money;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/**
 * Class Payment
 *
 * Represents a payment resource with various properties
 * including amount, network, status, timestamps, etc.
 */
final class Payment implements ModelInterface
{
    private const TIME_ZONE = 'UTC';

    public function __construct(
        public readonly string $id,
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
     * - id (string)
     * - amount (float)
     * - currency (string)
     * - createdAt (int timestamp OR date string)
     * - network (string)
     * - status (string)
     *
     * Optional fields:
     * - fees (float)
     * - netAmount (float)
     * - completedAt (int|string)
     * - notifiedAt (int|string)
     * - phoneNumber (string)
     * - country (string)
     *
     * @param array<string,mixed> $data
     *
     * @throws InvalidArgumentException if required, fields are missing or invalid
     */
    public static function fromArray(array $data): self
    {

        $get = static function (array $dataRaw, array $keys, $default = null): mixed {
            foreach ($keys as $key) {
                if (array_key_exists($key, $dataRaw)) {
                    return $dataRaw[$key];
                }
            }

            return $default;
        };

        // Required fields
        $id = $get($data, ['id']);
        $amountVal = $get($data, ['amount']);
        $currency = $get($data, ['currency']);
        $createdVal = $get($data, ['created_at', 'createdAt']);
        $network = $get($data, ['network']);
        $status = $get($data, ['status']);

        self::assertRequired([
            'id' => $id,
            'amount' => $amountVal,
            'currency' => $currency,
            'created_at' => $createdVal,
            'network' => $network,
            'status' => $status,
        ]);

        if (!is_scalar($id)) {
            throw new InvalidArgumentException('Field "id" must be a scalar string.');
        }

        if (!is_numeric($amountVal)) {
            throw new InvalidArgumentException('Field "amount" must be numeric.');
        }

        if (!is_scalar($currency)) {
            throw new InvalidArgumentException('Field "currency" must be a string.');
        }

        $amount = new Money(
            amount: (float)$amountVal,
            currency: Currency::from((string)$currency)
        );

        $createdAt = self::parseDateTime((string)$createdVal);

        $completedRaw = $get($data, ['completed_at', 'completedAt']);
        $notifiedRaw = $get($data, ['notified_at', 'notifiedAt']);

        return new self(
            id: (string)$id,
            amount: $amount,
            createdAt: $createdAt,
            network: (string)$network,
            status: (string)$status,
            fees: ($fees = $get($data, ['fees'])) !== null ? (float)$fees : null,
            netAmount: ($net = $get($data, ['net_amount', 'netAmount'])) !== null ? (float)$net : null,
            completedAt: $completedRaw !== null ? self::parseDateTime($completedRaw) : null,
            notifiedAt: $notifiedRaw !== null ? self::parseDateTime($notifiedRaw) : null,
            phoneNumber: $get($data, ['phone_number', 'phoneNumber']),
            country: $get($data, ['country'])
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
            'createdAt' => $this->createdAt->format(DateTimeInterface::ATOM),
            'network' => $this->network,
            'status' => $this->status,
            'fees' => $this->fees,
            'netAmount' => $this->netAmount,
            'completedAt' => $this->completedAt?->format(DateTimeInterface::ATOM),
            'notifiedAt' => $this->notifiedAt?->format(DateTimeInterface::ATOM),
            'phoneNumber' => $this->phoneNumber,
            'country' => $this->country,
        ];
    }

    /** Helper method to parse date/time from either a numeric timestamp or a string. */
    private static function parseDateTime(int|string $value): DateTimeInterface
    {
        $dateTime = is_numeric($value)
            ? (new DateTimeImmutable())->setTimestamp((int)$value)
            : new DateTimeImmutable((string)$value);

        return $dateTime->setTimezone(new DateTimeZone(self::TIME_ZONE));
    }

    /**
     * Validate required fields.
     *
     * @param array<string,mixed> $fields
     */
    private static function assertRequired(array $fields): void
    {
        foreach ($fields as $name => $value) {
            if ($value === null) {
                throw new InvalidArgumentException(sprintf(
                    'Missing required field "%s" for Payment creation.',
                    $name
                ));
            }
        }
    }
}
