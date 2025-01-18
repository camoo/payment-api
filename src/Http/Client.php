<?php

declare(strict_types=1);

namespace Camoo\Payment\Http;

use Camoo\Http\Curl\Domain\Client\ClientInterface;
use Camoo\Http\Curl\Domain\Entity\Configuration;
use Camoo\Http\Curl\Domain\Response\ResponseInterface;
use Camoo\Http\Curl\Infrastructure\Client as HttpClient;
use Camoo\Payment\Enum\Endpoint;
use Camoo\Payment\Exception\ApiException;

/**
 * Class Client
 *
 * Sends HTTP requests to the Camoo Payment API.
 */
class Client
{
    private const URL = 'https://api.camoo.cm/%s/payment';

    private const DEFAULT_API_VERSION = 'v1';

    private const HTTP_OK = 200;

    /**
     * Constructor
     *
     * @param string               $apiKey     Your API key
     * @param string               $apiSecret  Your API secret
     * @param ClientInterface|null $httpClient Optional custom HTTP client
     * @param bool                 $debug      Whether to enable debug mode
     * @param string|null          $apiVersion API version to use (defaults to v1)
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiSecret,
        private ?ClientInterface $httpClient = null,
        private readonly bool $debug = false,
        private readonly ?string $apiVersion = null
    ) {
    }

    public static function create(string $apiKey, string $apiSecret, bool $debug = false, ?string $apiVersion = null): self
    {
        return new self($apiKey, $apiSecret, null, $debug, $apiVersion);
    }

    /**
     * Send a GET request to the API.
     *
     * @param Endpoint            $endpoint  The endpoint to send the request to
     * @param array<string,mixed> $parameter Optional query parameters
     *
     * @return ResponseInterface The raw HTTP response
     */
    public function get(Endpoint $endpoint, array $parameter = []): ResponseInterface
    {
        $uri = $this->buildUri($endpoint, $parameter);

        return $this->getHttpClient()->get($uri, $this->getHeaders());
    }

    /**
     * Send a POST request to the API.
     *
     * @param Endpoint            $endpoint The endpoint to send the request to
     * @param array<string,mixed> $data     The data to send in the request body
     *
     * @return ResponseInterface The raw HTTP response
     */
    public function post(Endpoint $endpoint, array $data): ResponseInterface
    {
        $uri = $this->buildUri($endpoint);

        return $this->getHttpClient()->post($uri, $data, $this->getHeaders());
    }

    /**
     * Handle the response from the /account endpoint (or similar).
     *
     * @param ResponseInterface $response The raw HTTP response
     *
     * @throws ApiException If the response status code != 200 or if JSON is invalid
     *
     * @return array<string,mixed> The decoded JSON response
     */
    public function handleRequestResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getJson(); // Typically returns decoded JSON as an array

        if ($statusCode !== self::HTTP_OK) {
            throw new ApiException($body['message'] ?? 'Unknown error', $statusCode);
        }

        return $body;
    }

    /**
     * Build the complete URI string with an optional query string.
     *
     * @param array<string,mixed> $parameter
     */
    private function buildUri(Endpoint $endpoint, array $parameter = []): string
    {
        $baseUri = sprintf(self::URL, $this->apiVersion ?? self::DEFAULT_API_VERSION);
        if (!empty($parameter)) {
            return $baseUri . $endpoint->value . '?' . http_build_query($parameter);
        }

        return $baseUri . $endpoint->value;
    }

    /** Lazily instantiate and return the HTTP client. */
    private function getHttpClient(): ClientInterface
    {
        if ($this->httpClient === null) {
            $config = Configuration::create();
            if ($this->debug) {
                $config->setDebug(true);
            }

            $this->httpClient = new HttpClient($config);
        }

        return $this->httpClient;
    }

    /**
     * Get the headers to send with each request.
     *
     * @return array<string,string> The headers
     */
    private function getHeaders(): array
    {
        return [
            'X-Api-Key' => $this->apiKey,
            'X-Api-Secret' => $this->apiSecret,
            'X-Api-Version' => $this->apiVersion ?? self::DEFAULT_API_VERSION,
            'X-Api-Debug' => $this->debug ? 'true' : 'false',
            'X-PHP-Version' => PHP_VERSION,
        ];
    }
}
