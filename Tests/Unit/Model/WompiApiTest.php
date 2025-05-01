<?php
declare(strict_types=1);

namespace Saulmoralespa\WompiPa\Test\Unit\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Saulmoralespa\WompiPa\Gateway\Config\WompiPa;
use Saulmoralespa\WompiPa\Logger\Logger;
use Saulmoralespa\WompiPa\Model\WompiApi;

class WompiApiTest extends TestCase
{
    /**
     * @var WompiPa|MockObject
     */
    private $configMock;

    /**
     * @var Logger|MockObject
     */
    private $loggerMock;

    /**
     * @var Client|MockObject
     */
    private $clientMock;

    /**
     * @var WompiApi
     */
    private $wompiApi;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(WompiPa::class);
        $this->loggerMock = $this->createMock(Logger::class);
        $this->clientMock = $this->createMock(Client::class);

        // Create a partial mock of WompiApi to override getClient method
        $this->wompiApi = $this->getMockBuilder(WompiApi::class)
            ->setConstructorArgs([$this->configMock, $this->loggerMock])
            ->onlyMethods(['getClient'])
            ->getMock();
    }

    public function testGet(): void
    {
        $storeId = 1;
        $transactionId = 'test-transaction-123';
        $responseData = [
            'data' => [
                'id' => $transactionId,
                'amount_in_cents' => 5000,
                'status' => 'APPROVED',
                'reference' => 'order-123'
            ]
        ];
        $responseJson = json_encode($responseData);

        // Mock the response stream
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->expects($this->once())
            ->method('getContents')
            ->willReturn($responseJson);

        // Mock the HTTP response
        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock);

        // Configure client mock
        $this->clientMock->expects($this->once())
            ->method('get')
            ->with('transactions/' . $transactionId)
            ->willReturn($responseMock);

        // Configure WompiApi to return our client mock
        $this->wompiApi->expects($this->once())
            ->method('getClient')
            ->with($storeId)
            ->willReturn($this->clientMock);

        $result = $this->wompiApi->get($transactionId, $storeId);
        $this->assertEquals($responseData, $result);
    }

    public function testGetWithException(): void
    {
        $storeId = 1;
        $transactionId = 'test-transaction-error';

        // Create request exception
        $requestMock = $this->createMock(RequestInterface::class);
        $exception = new RequestException(
            'API Error',
            $requestMock
        );

        // Configure client mock to throw exception
        $this->clientMock->expects($this->once())
            ->method('get')
            ->with('transactions/' . $transactionId)
            ->willThrowException($exception);

        // Configure WompiApi to return our client mock
        $this->wompiApi->expects($this->once())
            ->method('getClient')
            ->with($storeId)
            ->willReturn($this->clientMock);

        // Logger should log the error
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error fetching transaction'));

        $result = $this->wompiApi->get($transactionId, $storeId);
        $this->assertNull($result);
    }

    public function testGetSignature(): void
    {
        $storeId = 1;
        $transactionId = 'txn_123';
        $status = 'APPROVED';
        $amountInCents = 10000;
        $timestamp = 1617123456;
        $eventsKey = 'test-events-key-123';
        $expectedSignature = hash('sha256', $transactionId . $status . $amountInCents . $timestamp . $eventsKey);

        // Configure config mock to return events key
        $this->configMock->expects($this->once())
            ->method('getEventsKey')
            ->with($storeId)
            ->willReturn($eventsKey);

        $signature = $this->wompiApi->getSignature($transactionId, $status, $amountInCents, $timestamp, $storeId);
        $this->assertEquals($expectedSignature, $signature);
    }

    public function testGetApiBaseUrlForProduction(): void
    {
        // Create a non-mocked instance for testing protected methods
        $wompiApi = new class($this->configMock, $this->loggerMock) extends WompiApi {
            public function publicGetApiBaseUrl(?int $storeId = null): string
            {
                return $this->getApiBaseUrl($storeId);
            }
        };

        $storeId = 1;

        // Configure config mock for production environment
        $this->configMock->expects($this->once())
            ->method('isTest')
            ->with($storeId)
            ->willReturn(false);

        $this->assertEquals(
            WompiApi::API_BASE_URL,
            $wompiApi->publicGetApiBaseUrl($storeId)
        );
    }

    public function testGetApiBaseUrlForSandbox(): void
    {
        // Create a non-mocked instance for testing protected methods
        $wompiApi = new class($this->configMock, $this->loggerMock) extends WompiApi {
            public function publicGetApiBaseUrl(?int $storeId = null): string
            {
                return $this->getApiBaseUrl($storeId);
            }
        };

        $storeId = 1;

        // Configure config mock for test environment
        $this->configMock->expects($this->once())
            ->method('isTest')
            ->with($storeId)
            ->willReturn(true);

        $this->assertEquals(
            WompiApi::SANDBOX_API_BASE_URL,
            $wompiApi->publicGetApiBaseUrl($storeId)
        );
    }

    public function testGetClient(): void
    {
        // Create a non-mocked instance for testing protected methods
        $wompiApi = new class($this->configMock, $this->loggerMock) extends WompiApi {
            public function publicGetClient(?int $storeId = null): Client
            {
                return $this->getClient($storeId);
            }
        };

        $storeId = 1;
        $apiKey = 'test-api-key';
        $baseUrl = WompiApi::SANDBOX_API_BASE_URL;

        // Configure mocks
        $this->configMock->expects($this->once())
            ->method('isTest')
            ->with($storeId)
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getPublicKey')
            ->with($storeId)
            ->willReturn($apiKey);

        $client = $wompiApi->publicGetClient($storeId);

        $this->assertInstanceOf(Client::class, $client);

        // Unfortunately we can't easily test the client configuration without
        // exposing internal properties. The creation itself is sufficient to verify
        // the method works as expected.
    }
}
