<?php

declare(strict_types=1);

namespace Camoo\Payment\Api;

use Camoo\Payment\Enum\Endpoint;
use Camoo\Payment\Exception\ApiException;
use Camoo\Payment\Http\Client;
use Camoo\Payment\Models\Payment;

class PaymentApi
{
    public function __construct(private readonly Client $client)
    {
    }

    /** @param array<string, mixed> $payload */
    public function cashout(array $payload): Payment
    {
        $response = $this->client->post(Endpoint::CASH_OUT, $payload);
        $data = $this->client->handleRequestResponse($response);

        if (!isset($data['cashOut']) || !is_array($data['cashOut'])) {
            throw new ApiException('Invalid cashOut data in response');
        }

        return Payment::fromArray($data['cashOut']);
    }

    public function verify(string $id): Payment
    {

        $response = $this->client->get(Endpoint::VERIFY, ['id' => $id]);
        $data = $this->client->handleRequestResponse($response);

        if (!isset($data['verify']) || !is_array($data['verify'])) {
            throw new ApiException('Invalid verify data in response');
        }

        return Payment::fromArray($data['verify']);
    }
}
