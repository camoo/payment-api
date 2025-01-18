<?php

declare(strict_types=1);

namespace Camoo\Payment\Tests\Api;

use Camoo\Http\Curl\Domain\Response\ResponseInterface;
use Camoo\Payment\Api\PaymentApi;
use Camoo\Payment\Enum\Endpoint;
use Camoo\Payment\Exception\ApiException;
use Camoo\Payment\Http\Client;
use Camoo\Payment\Models\Payment;
use Camoo\Payment\ValueObject\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PaymentApi::class)] final class PaymentApiTest extends TestCase
{
    public function testCashoutSuccess(): void
    {
        $mockClient = $this->createMock(Client::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        // 1) Expect a POST call to CASH_OUT endpoint with specific payload
        $mockClient->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo(Endpoint::CASH_OUT),
                $this->equalTo(['foo' => 'bar'])
            )
            ->willReturn($mockResponse);

        // 2) Mock handleRequestResponse to return an array that includes 'cashOut' and top-level Payment fields
        $mockClient->expects($this->once())
            ->method('handleRequestResponse')
            ->with($this->equalTo($mockResponse))
            ->willReturn([
                // The presence of 'cashOut' ensures no ApiException is thrown
                'cashOut' => [
                    'id' => 123,
                    'amount' => 1000,
                    'currency' => 'XAF',
                    'createdAt' => time(),
                    'network' => 'MTN',
                    'status' => 'success',
                ],

            ]);

        $paymentApi = new PaymentApi($mockClient);
        $payment = $paymentApi->cashout(['foo' => 'bar']);

        // Verify we got a valid Payment model back
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame(123, $payment->id);
        $this->assertInstanceOf(Money::class, $payment->amount);
        $this->assertSame(1000.0, $payment->amount->amount);
        $this->assertSame('XAF', $payment->amount->currency->value);
        $this->assertSame('MTN', $payment->network);
        $this->assertSame('success', $payment->status);
    }

    public function testCashoutThrowsExceptionOnMissingCashOutData(): void
    {
        $mockClient = $this->createMock(Client::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockClient->expects($this->once())
            ->method('post')
            ->with($this->equalTo(Endpoint::CASH_OUT), $this->equalTo([]))
            ->willReturn($mockResponse);

        // Missing 'cashOut' key => triggers exception
        $mockClient->expects($this->once())
            ->method('handleRequestResponse')
            ->willReturn([
                'id' => 123,
                'amount' => 1000,
                'currency' => 'XAF',
                'createdAt' => time(),
                'network' => 'MTN',
                'status' => 'success',
            ]);

        $paymentApi = new PaymentApi($mockClient);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid cashOut data in response');

        // Should throw an exception
        $paymentApi->cashout([]);
    }

    public function testVerifySuccess(): void
    {
        $mockClient = $this->createMock(Client::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        // 1) Expect a GET call to VERIFY endpoint with ['id' => 'TX123']
        $mockClient->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo(Endpoint::VERIFY),
                $this->equalTo(['id' => 'TX123'])
            )
            ->willReturn($mockResponse);

        // 2) Mock handleRequestResponse to return an array that includes 'verify' and top-level Payment fields
        $mockClient->expects($this->once())
            ->method('handleRequestResponse')
            ->willReturn([
                'verify' => [
                    'id' => 999,
                    'amount' => 2500,
                    'currency' => 'XAF',
                    'createdAt' => time(),
                    'network' => 'ORANGE',
                    'status' => 'pending',
                ],

            ]);

        $paymentApi = new PaymentApi($mockClient);
        $payment = $paymentApi->verify('TX123');

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame(999, $payment->id);
        $this->assertSame(2500.0, $payment->amount->amount);
        $this->assertSame('XAF', $payment->amount->currency->value);
        $this->assertSame('ORANGE', $payment->network);
        $this->assertSame('pending', $payment->status);
    }

    public function testVerifyThrowsExceptionOnMissingVerifyData(): void
    {
        $mockClient = $this->createMock(Client::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(Endpoint::VERIFY), $this->equalTo(['id' => 'TX123']))
            ->willReturn($mockResponse);

        // Missing 'verify' key => triggers exception
        $mockClient->expects($this->once())
            ->method('handleRequestResponse')
            ->willReturn([
                'id' => 999,
                'amount' => 2500,
                'currency' => 'XAF',
                'createdAt' => time(),
                'network' => 'ORANGE',
                'status' => 'pending',
            ]);

        $paymentApi = new PaymentApi($mockClient);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid verify data in response');

        $paymentApi->verify('TX123');
    }
}
