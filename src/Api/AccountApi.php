<?php

declare(strict_types=1);

namespace Camoo\Payment\Api;

use Camoo\Payment\Enum\Endpoint;
use Camoo\Payment\Exception\ApiException;
use Camoo\Payment\Http\Client;
use Camoo\Payment\Models\Account;

class AccountApi
{
    public function __construct(private readonly Client $client)
    {
    }

    public function get(): Account
    {
        $response = $this->client->get(Endpoint::ACCOUNT);
        $data = $this->client->handleRequestResponse($response);

        // Optionally validate certain fields exist
        if (!isset($data['account']) || !is_array($data['account'])) {
            throw new ApiException('Invalid account data in response');
        }

        return Account::fromArray($data);
    }
}
