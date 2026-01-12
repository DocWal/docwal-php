<?php

namespace DocWal\Resources;

use DocWal\DocWalClient;

/**
 * Team management methods
 */
class TeamResource
{
    /** @var DocWalClient */
    private $client;

    public function __construct(DocWalClient $client)
    {
        $this->client = $client;
    }

    /**
     * List all team members and pending invitations
     *
     * @return array Response with members, pending_invitations, stats
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function list(): array
    {
        return $this->client->request('GET', '/institutions/team/');
    }

    /**
     * Check if email is valid for invitation
     *
     * @param string $email Email address to check
     * @return array Validation result with recommendation
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function checkEmail(string $email): array
    {
        return $this->client->request('POST', '/institutions/team/check-email/', [
            'json' => ['email' => $email],
        ]);
    }

    /**
     * Invite team member
     *
     * @param array $params Parameters:
     *   - email (string, required): Email address (must use institution domain)
     *   - role (string, optional): Role (owner, admin, issuer) - default: issuer
     *   - send_email (bool, optional): Send invitation email - default: true
     *   - add_directly (bool, optional): Add directly if user exists - default: false
     *
     * @return array Response with invitation or member details
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function invite(array $params): array
    {
        return $this->client->request('POST', '/institutions/team/invite/', [
            'json' => [
                'email' => $params['email'],
                'role' => $params['role'] ?? 'issuer',
                'send_email' => $params['send_email'] ?? true,
                'add_directly' => $params['add_directly'] ?? false,
            ],
        ]);
    }

    /**
     * Update team member role
     *
     * @param string $memberId Team member ID
     * @param string $role New role (owner, admin, issuer)
     * @return array Success message with updated member
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function updateRole(string $memberId, string $role): array
    {
        return $this->client->request('PATCH', "/institutions/team/members/{$memberId}/role/", [
            'json' => ['role' => $role],
        ]);
    }

    /**
     * Deactivate team member (soft delete)
     *
     * @param string $memberId Team member ID
     * @param string|null $reason Optional reason for deactivation
     * @return array Success message
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function deactivate(string $memberId, ?string $reason = null): array
    {
        $data = [];
        if ($reason !== null) {
            $data['reason'] = $reason;
        }

        return $this->client->request('POST', "/institutions/team/members/{$memberId}/deactivate/", [
            'json' => $data,
        ]);
    }

    /**
     * Reactivate team member
     *
     * @param string $memberId Team member ID
     * @return array Success message
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function reactivate(string $memberId): array
    {
        return $this->client->request('POST', "/institutions/team/members/{$memberId}/reactivate/");
    }

    /**
     * Remove team member (hard delete)
     *
     * @param string $memberId Team member ID
     * @return array Success message
     * @throws \DocWal\Exceptions\DocWalException
     */
    public function remove(string $memberId): array
    {
        return $this->client->request('DELETE', "/institutions/team/members/{$memberId}/remove/");
    }
}
