<?php

namespace DocWal\Resources;

use DocWal\DocWalClient;

/**
 * Template management methods
 */
class TemplatesResource
{
    /** @var DocWalClient */
    private $client;

    public function __construct(DocWalClient $client)
    {
        $this->client = $client;
    }

    /**
     * List all active templates
     *
     * @return array List of template arrays
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function list(): array
    {
        return $this->client->request('GET', '/templates/');
    }

    /**
     * Get template by ID
     *
     * @param string $templateId Template ID
     * @return array Template details
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function get(string $templateId): array
    {
        return $this->client->request('GET', "/templates/{$templateId}/");
    }

    /**
     * Create a new credential template
     *
     * @param array $params Parameters:
     *   - name (string, required): Template name
     *   - description (string, required): Template description
     *   - credential_type (string, required): Type (certificate, diploma, transcript, etc.)
     *   - schema (array, required): Field definitions array
     *   - version (string, optional): Template version (default: "1.0")
     *
     * @return array Created template
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function create(array $params): array
    {
        return $this->client->request('POST', '/templates/', [
            'json' => [
                'name' => $params['name'],
                'description' => $params['description'],
                'credential_type' => $params['credential_type'],
                'schema' => $params['schema'],
                'version' => $params['version'] ?? '1.0',
            ],
        ]);
    }

    /**
     * Update template (creates new version if schema changes)
     *
     * @param string $templateId Template ID
     * @param array $updates Fields to update
     * @return array Updated template
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function update(string $templateId, array $updates): array
    {
        return $this->client->request('PATCH', "/templates/{$templateId}/", [
            'json' => $updates,
        ]);
    }

    /**
     * Deactivate template (soft delete)
     *
     * @param string $templateId Template ID
     * @return array Success message
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function delete(string $templateId): array
    {
        return $this->client->request('DELETE', "/templates/{$templateId}/");
    }
}
