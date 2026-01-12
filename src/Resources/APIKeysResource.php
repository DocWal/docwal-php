<?php

namespace DocWal\Resources;

use DocWal\DocWalClient;

/**
 * API key management methods
 */
class APIKeysResource
{
    /** @var DocWalClient */
    private $client;

    public function __construct(DocWalClient $client)
    {
        $this->client = $client;
    }

    /**
     * Generate new API key (Owner/Admin only)
     *
     * @return array Response with api_key, created_at, warning
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function generate(): array
    {
        return $this->client->request('POST', '/institutions/api-keys/generate/');
    }

    /**
     * Get API key information (masked)
     *
     * @return array API key info
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function info(): array
    {
        return $this->client->request('GET', '/institutions/api-keys/info/');
    }

    /**
     * Regenerate API key (revokes old, creates new)
     *
     * @return array Response with new api_key
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function regenerate(): array
    {
        return $this->client->request('POST', '/institutions/api-keys/regenerate/');
    }

    /**
     * Revoke current API key
     *
     * @return array Success message
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function revoke(): array
    {
        return $this->client->request('POST', '/institutions/api-keys/revoke/');
    }
}
