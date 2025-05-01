<?php

namespace Saulmoralespa\WompiPa\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Utils;
use GuzzleHttp\Exception\GuzzleException;
use Saulmoralespa\WompiPa\Gateway\Config\WompiPa;
use Saulmoralespa\WompiPa\Logger\Logger;

class WompiApi
{
    public const API_BASE_URL = "https://api.wompi.pa/";
    public const SANDBOX_API_BASE_URL = "https://api-sandbox.wompi.pa/";
    public const API_VERSION = "v1";

    /**
     * @param WompiPa $config
     * @param Logger $logger
     */
    public function __construct(
        protected WompiPa $config,
        protected Logger $logger
    ) {
    }

    /**
     * Get transaction details by transaction ID.
     *
     * @param string $transactionId
     * @param int|null $storeId
     * @return array|null
     */
    public function get(string $transactionId, int $storeId = null): ?array
    {
        $client = $this->getClient($storeId);

        try {
            $response = $client->get('transactions/' . $transactionId);
            return Utils::jsonDecode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $this->logger->error('Error fetching transaction: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the Wompi API client.
     *
     * @param int|null $storeId
     * @return Client
     */
    protected function getClient(int $storeId = null): Client
    {
        $baseUrl = $this->getApiBaseUrl($storeId);
        $apiKey = $this->config->getPublicKey($storeId);

        return new Client([
            'base_uri' => $baseUrl . self::API_VERSION . '/',
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 30
        ]);
    }

    /**
     * Get the base URL for API requests.
     *
     * @param int|null $storeId
     * @return string
     */
    protected function getApiBaseUrl(?int $storeId = null): string
    {
        return $this->config->isTest($storeId)
            ? self::SANDBOX_API_BASE_URL
            : self::API_BASE_URL;
    }

    /**
     * Get signature for the webhook notification.
     *
     * @param string $transactionId
     * @param string $status
     * @param int $amountInCents
     * @param int $timestamp
     * @param int|null $storeId
     * @return string
     */
    public function getSignature(
        string $transactionId,
        string $status,
        int $amountInCents,
        int $timestamp,
        int $storeId = null
    ): string {
        return hash(
            'sha256',
            $transactionId . $status . $amountInCents . $timestamp . $this->config->getEventsKey($storeId)
        );
    }
}
