<?php

namespace DocWal\Resources;

use DocWal\DocWalClient;
use GuzzleHttp\Psr7\Utils;

/**
 * Credentials management methods
 */
class CredentialsResource
{
    /** @var DocWalClient */
    private $client;

    public function __construct(DocWalClient $client)
    {
        $this->client = $client;
    }

    /**
     * Issue a single credential
     *
     * @param array $params Parameters:
     *   - template_id (string, required): Template ID to use
     *   - individual_email (string, required): Recipient's email address
     *   - credential_data (array, required): Dictionary of credential fields
     *   - document_file (resource|string, optional): PDF file to attach
     *   - expires_at (string, optional): Expiration date (ISO format)
     *   - claim_token_expires_hours (int, optional): Claim link expiry (default: 720)
     *
     * @return array Response with doc_id, document_hash, status, claim_token
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function issue(array $params): array
    {
        $templateId = $params['template_id'];
        $individualEmail = $params['individual_email'];
        $credentialData = $params['credential_data'];
        $documentFile = $params['document_file'] ?? null;
        $expiresAt = $params['expires_at'] ?? null;
        $claimTokenExpiresHours = $params['claim_token_expires_hours'] ?? 720;

        if ($documentFile) {
            $multipart = [
                [
                    'name' => 'template_id',
                    'contents' => $templateId,
                ],
                [
                    'name' => 'individual_email',
                    'contents' => $individualEmail,
                ],
                [
                    'name' => 'credential_data',
                    'contents' => json_encode($credentialData),
                ],
                [
                    'name' => 'claim_token_expires_hours',
                    'contents' => (string) $claimTokenExpiresHours,
                ],
            ];

            if ($expiresAt) {
                $multipart[] = [
                    'name' => 'expires_at',
                    'contents' => $expiresAt,
                ];
            }

            $multipart[] = [
                'name' => 'document_file',
                'contents' => $documentFile,
                'filename' => 'document.pdf',
            ];

            return $this->client->request('POST', '/credentials/issue/', [
                'multipart' => $multipart,
            ]);
        } else {
            $data = [
                'template_id' => $templateId,
                'individual_email' => $individualEmail,
                'credential_data' => $credentialData,
                'claim_token_expires_hours' => $claimTokenExpiresHours,
            ];

            if ($expiresAt) {
                $data['expires_at'] = $expiresAt;
            }

            return $this->client->request('POST', '/credentials/issue/', [
                'json' => $data,
            ]);
        }
    }

    /**
     * Issue multiple credentials in batch
     *
     * @param string $templateId Template ID to use
     * @param array $credentials List of credential arrays with individual_email and credential_data
     * @param bool $sendNotifications Send claim emails to recipients
     * @return array Response with total_rows, success_count, failure_count, results
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function batchIssue(string $templateId, array $credentials, bool $sendNotifications = true): array
    {
        return $this->client->request('POST', '/credentials/batch/', [
            'json' => [
                'template_id' => $templateId,
                'credentials' => $credentials,
                'send_notifications' => $sendNotifications,
            ],
        ]);
    }

    /**
     * Batch upload credentials with ZIP file (CSV/JSON + PDFs)
     *
     * @param string $templateId Template ID to use
     * @param resource|string $file ZIP file containing credentials.csv and documents/ folder
     * @param bool $sendNotifications Send claim emails to recipients
     * @return array Response with total_rows, success_count, failure_count, results
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function batchUpload(string $templateId, $file, bool $sendNotifications = true): array
    {
        return $this->client->request('POST', '/credentials/batch-upload/', [
            'multipart' => [
                [
                    'name' => 'template_id',
                    'contents' => $templateId,
                ],
                [
                    'name' => 'send_notifications',
                    'contents' => $sendNotifications ? 'true' : 'false',
                ],
                [
                    'name' => 'file',
                    'contents' => $file,
                    'filename' => 'batch.zip',
                ],
            ],
        ]);
    }

    /**
     * List all credentials issued by your institution
     *
     * @param int $limit Number of results per page
     * @param int $offset Pagination offset
     * @return array List of credential arrays
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function list(int $limit = 100, int $offset = 0): array
    {
        return $this->client->request('GET', '/credentials/', [
            'query' => [
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);
    }

    /**
     * Get credential details by doc_id
     *
     * @param string $docId Credential document ID
     * @return array Credential details
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function get(string $docId): array
    {
        return $this->client->request('GET', "/credentials/{$docId}/");
    }

    /**
     * Revoke a credential
     *
     * @param string $docId Credential document ID
     * @param string $reason Reason for revocation
     * @return array Success message
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function revoke(string $docId, string $reason): array
    {
        return $this->client->request('POST', "/credentials/{$docId}/revoke/", [
            'json' => ['reason' => $reason],
        ]);
    }

    /**
     * Resend claim link email to recipient
     *
     * @param string $docId Credential document ID
     * @param int $claimTokenExpiresHours New expiration (default: 30 days)
     * @return array Response with message, claim_token, claim_token_expires, recipient_email
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function resendClaimLink(string $docId, int $claimTokenExpiresHours = 720): array
    {
        return $this->client->request('POST', "/credentials/{$docId}/resend-claim/", [
            'json' => ['claim_token_expires_hours' => $claimTokenExpiresHours],
        ]);
    }

    /**
     * Download credential file (PDF)
     *
     * @param string $docId Credential document ID
     * @return string PDF file content
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function download(string $docId): string
    {
        $response = $this->client->getHttpClient()->get("/credentials/{$docId}/download/");
        return (string) $response->getBody();
    }
}
