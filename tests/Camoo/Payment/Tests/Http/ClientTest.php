<?php

declare(strict_types=1);

namespace Camoo\Payment\Tests\Http;

use Camoo\Http\Curl\Domain\Client\ClientInterface;
use Camoo\Http\Curl\Domain\Response\ResponseInterface;
use Camoo\Payment\Enum\Endpoint;
use Camoo\Payment\Exception\ApiException;
use Camoo\Payment\Http\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Client::class)] final class ClientTest extends TestCase
{
    public function testGetSuccess(): void
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        // Mock the response from the HTTP client
        $mockHttpClient->expects($this->once())
            ->method('get')
            ->with(
                // URL with query string
                $this->equalTo('https://api.camoo.cm/v1/payment/account?foo=bar'),
                $this->arrayHasKey('X-Api-Key') // a quick check on headers
            )
            ->willReturn($mockResponse);

        // The response returns 200 and some JSON
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getJson')->willReturn(['success' => true]);

        $client = new Client('API_KEY', 'API_SECRET', $mockHttpClient);
        $response = $client->get(Endpoint::ACCOUNT, ['foo' => 'bar']);

        // The raw HTTP response is returned from the client
        $this->assertSame($mockResponse, $response);
    }

    public function testPostSuccess(): void
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockHttpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo('https://api.camoo.cm/v1/payment/cashout'),
                $this->equalTo(['amount' => 1000]),
                $this->arrayHasKey('X-Api-Key')
            )
            ->willReturn($mockResponse);

        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getJson')->willReturn(['cashOut' => ['id' => 123]]);

        $client = new Client('API_KEY', 'API_SECRET', $mockHttpClient);
        $response = $client->post(Endpoint::CASH_OUT, ['amount' => 1000]);

        $this->assertSame($mockResponse, $response);
    }

    public function testHandleRequestResponseSuccess(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getJson')->willReturn(['foo' => 'bar']);

        $client = new Client('API_KEY', 'API_SECRET');
        $data = $client->handleRequestResponse($mockResponse);

        // Expect the returned array
        $this->assertSame(['foo' => 'bar'], $data);
    }

    public function testHandleRequestResponseThrowsApiExceptionOnError(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(400);
        $mockResponse->method('getJson')->willReturn(['message' => 'Bad request']);

        $client = new Client('API_KEY', 'API_SECRET');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Bad request');
        $this->expectExceptionCode(400);

        // This should throw an ApiException due to non-200 status code
        $client->handleRequestResponse($mockResponse);
    }
}
