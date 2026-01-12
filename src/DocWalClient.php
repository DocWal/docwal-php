<?php

namespace DocWal;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use DocWal\Resources\CredentialsResource;
use DocWal\Resources\TemplatesResource;
use DocWal\Resources\APIKeysResource;
use DocWal\Resources\TeamResource;
use DocWal\Exceptions\DocWalException;
use DocWal\Exceptions\AuthenticationException;
use DocWal\Exceptions\ValidationException;
use DocWal\Exceptions\NotFoundException;
use DocWal\Exceptions\RateLimitException;

/**
 * DocWal API Client
 *
 * Official PHP SDK for DocWal API - Issue and manage verifiable digital credentials.
 *
 * Usage:
 * $client = new DocWalClient('docwal_live_xxxxx');
 *
 * // Issue a credential
 * $result = $client->credentials->issue([
 *     'template_id' => 'template-123',
 *     'individual_email' => 'student@example.com',
 *     'credential_data' => [
 *         'student_name' => 'John Doe',
 *         'degree' => 'Bachelor of Science',
 *         'graduation_date' => '2024-05-15'
 *     ]
 * ]);
 */
class DocWalClient
{
    /** @var Client */
    private $httpClient;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $baseUrl;

    /** @var CredentialsResource */
    public $credentials;

    /** @var TemplatesResource */
    public $templates;

    /** @var APIKeysResource */
    public $apiKeys;

    /** @var TeamResource */
    public $team;

    /**
     * Initialize DocWal client
     *
     * @param string $apiKey Your DocWal API key (get from Settings → API Keys)
     * @param string $baseUrl API base URL (default: production)
     * @param int $timeout Request timeout in seconds
     */
    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://docwal.com/api',
        int $timeout = 30
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');

        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $timeout,
            'headers' => [
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Initialize resources
        $this->credentials = new CredentialsResource($this);
        $this->templates = new TemplatesResource($this);
        $this->apiKeys = new APIKeysResource($this);
        $this->team = new TeamResource($this);
    }

    /**
     * Make HTTP request to DocWal API
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $options Request options
     * @return array Response data
     * @throws DocWalException
     */
    public function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            $body = (string) $response->getBody();

            return $body ? json_decode($body, true) : [];
        } catch (GuzzleException $e) {
            if (method_exists($e, 'hasResponse') && $e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $body = (string) $response->getBody();
                $data = json_decode($body, true);
                $message = $data['error'] ?? $e->getMessage();

                switch ($statusCode) {
                    case 401:
                        throw new AuthenticationException($message, $statusCode);
                    case 400:
                        throw new ValidationException($message, $statusCode);
                    case 404:
                        throw new NotFoundException($message, $statusCode);
                    case 429:
                        throw new RateLimitException($message, $statusCode);
                    default:
                        throw new DocWalException($message, $statusCode);
                }
            }

            throw new DocWalException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Get HTTP client
     *
     * @return Client
     */
    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * Get base URL
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
