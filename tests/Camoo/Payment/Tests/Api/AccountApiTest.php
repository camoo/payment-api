<?php

declare(strict_types=1);

namespace Camoo\Payment\Tests\Api;

use Camoo\Http\Curl\Domain\Response\ResponseInterface;
use Camoo\Payment\Api\AccountApi;
use Camoo\Payment\Enum\Endpoint;
use Camoo\Payment\Exception\ApiException;
use Camoo\Payment\Http\Client;
use Camoo\Payment\Models\Account;
use Camoo\Payment\ValueObject\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AccountApi::class)] final class AccountApiTest extends TestCase
{
    public function testGetSuccess(): void
    {
        $mockClient = $this->createMock(Client::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        // Mock the get() method to return our $mockResponse.
        $mockClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(Endpoint::ACCOUNT))
            ->willReturn($mockResponse);

        // Mock the handleRequestResponse() method to return valid account data.
        $mockClient->expects($this->once())
            ->method('handleRequestResponse')
            ->with($this->equalTo($mockResponse))
            ->willReturn([
                'account' => [
                    'balance' => 100.0,
                    'currency' => 'XAF',
                    'date' => '2023-01-01 10:00:00',
                ],
            ]);

        $api = new AccountApi($mockClient);
        $result = $api->get();

        // Asserts that an Account model is returned
        $this->assertInstanceOf(Account::class, $result);
        $this->assertInstanceOf(Money::class, $result->balance);
        $this->assertSame(100.0, $result->balance->amount);
        $this->assertSame('XAF', $result->balance->currency->value);
        $this->assertSame('2023-01-01 10:00:00', $result->viewedAt->format('Y-m-d H:i:s'));
    }

    public function testGetThrowsApiExceptionWhenAccountKeyMissing(): void
    {
        $mockClient = $this->createMock(Client::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        // Return a response without 'account' key
        $mockClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(Endpoint::ACCOUNT))
            ->willReturn($mockResponse);

        $mockClient->expects($this->once())
            ->method('handleRequestResponse')
            ->with($this->equalTo($mockResponse))
            ->willReturn(['wrongKey' => []]);

        $api = new AccountApi($mockClient);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid account data in response');

        // Should throw ApiException due to missing 'account' data
        $api->get();
    }
}
