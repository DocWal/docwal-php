# DocWal PHP SDK

Official PHP SDK for DocWal API - Issue and manage verifiable digital credentials.

## Requirements

- PHP 7.4 or higher
- Composer

## Installation

```bash
composer require docwal/sdk
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use DocWal\DocWalClient;

// Initialize client with your API key
$client = new DocWalClient('docwal_live_xxxxx');

// Issue a credential
$result = $client->credentials->issue([
    'template_id' => 'template-123',
    'individual_email' => 'student@example.com',
    'credential_data' => [
        'student_name' => 'John Doe',
        'degree' => 'Bachelor of Science',
        'major' => 'Computer Science',
        'graduation_date' => '2024-05-15',
        'gpa' => '3.8'
    ]
]);

echo "Credential issued! Doc ID: {$result['doc_id']}\n";
echo "Claim token: {$result['claim_token']}\n";
```

## Authentication

Get your API key from your DocWal dashboard:
1. Login to https://docwal.com
2. Navigate to Settings → API Keys
3. Click "Generate API Key"
4. Copy and store securely

**Requirements:**
- Pilot tier or above
- Owner or Admin role

## Environment Configuration

```php
// Production (default)
$client = new DocWalClient('docwal_live_xxxxx');

// Staging
$client = new DocWalClient(
    'docwal_test_xxxxx',
    'https://sandbox.docwal.com/api'
);

// Custom timeout (seconds)
$client = new DocWalClient(
    'docwal_live_xxxxx',
    'https://docwal.com/api',
    60  // timeout in seconds
);
```

## Usage Examples

### Issue Single Credential

```php
// Basic credential
$result = $client->credentials->issue([
    'template_id' => 'template-123',
    'individual_email' => 'student@example.com',
    'credential_data' => [
        'student_name' => 'John Doe',
        'degree' => 'Bachelor of Science',
        'graduation_date' => '2024-05-15'
    ]
]);

// With PDF attachment
$pdfFile = fopen('certificate.pdf', 'r');
$result = $client->credentials->issue([
    'template_id' => 'template-123',
    'individual_email' => 'student@example.com',
    'credential_data' => ['student_name' => 'John Doe'],
    'document_file' => $pdfFile,
    'claim_token_expires_hours' => 168  // 7 days
]);
fclose($pdfFile);
```

### Batch Issue Credentials

```php
$credentialsList = [
    [
        'individual_email' => 'student1@example.com',
        'credential_data' => [
            'student_name' => 'Alice Smith',
            'degree' => 'Bachelor of Arts',
            'graduation_date' => '2024-05-15'
        ]
    ],
    [
        'individual_email' => 'student2@example.com',
        'credential_data' => [
            'student_name' => 'Bob Johnson',
            'degree' => 'Bachelor of Science',
            'graduation_date' => '2024-05-15'
        ]
    ]
];

$result = $client->credentials->batchIssue(
    'template-123',
    $credentialsList,
    true  // send notifications
);

echo "Success: {$result['success_count']}/{$result['total_rows']}\n";
```

### Batch Upload with ZIP

```php
// ZIP structure:
// batch_credentials.zip
// ├── credentials.csv
// └── documents/
//     ├── student001.pdf
//     ├── student002.pdf
//     └── student003.pdf

$zipFile = fopen('batch_credentials.zip', 'r');

$result = $client->credentials->batchUpload(
    'template-123',
    $zipFile,
    true  // send notifications
);

fclose($zipFile);

echo "Processed: {$result['total_rows']}\n";
echo "Success: {$result['success_count']}\n";
echo "Failed: {$result['failure_count']}\n";

foreach ($result['results'] as $item) {
    if ($item['status'] === 'success') {
        echo "Row {$item['row']}: {$item['doc_id']}\n";
    } else {
        echo "Row {$item['row']}: {$item['error']}\n";
    }
}
```

### List and Get Credentials

```php
// List all credentials
$credentials = $client->credentials->list(50, 0);

foreach ($credentials as $cred) {
    echo "{$cred['doc_id']}: {$cred['template_name']}\n";
}

// Get specific credential
$credential = $client->credentials->get('DOC123456');
echo "Issued to: {$credential['ownership']['individual_email']}\n";
$status = $credential['ownership']['is_claimed'] ? 'Claimed' : 'Pending';
echo "Status: {$status}\n";
```

### Revoke Credential

```php
$result = $client->credentials->revoke(
    'DOC123456',
    'Student expelled for academic misconduct'
);
echo $result['message'] . "\n";
```

### Resend Claim Link

```php
$result = $client->credentials->resendClaimLink(
    'DOC123456',
    168  // 7 days
);

echo "Sent to: {$result['recipient_email']}\n";
echo "Expires: {$result['claim_token_expires']}\n";
```

### Download Credential File

```php
// Download PDF file
$pdfContent = $client->credentials->download('DOC123456');
file_put_contents('credential.pdf', $pdfContent);
```

## Template Management

```php
// List templates
$templates = $client->templates->list();

// Get template
$template = $client->templates->get('template-123');

// Create template
$template = $client->templates->create([
    'name' => 'Bachelor Degree Certificate',
    'description' => 'Template for bachelor degree graduation certificates',
    'credential_type' => 'certificate',
    'schema' => [
        'student_name' => [
            'type' => 'string',
            'required' => true,
            'label' => 'Student Name'
        ],
        'degree' => [
            'type' => 'string',
            'required' => true,
            'label' => 'Degree Program'
        ],
        'graduation_date' => [
            'type' => 'date',
            'required' => true,
            'label' => 'Graduation Date'
        ]
    ],
    'version' => '1.0'
]);

// Update template
$client->templates->update('template-123', [
    'description' => 'Updated description'
]);

// Delete template (soft delete)
$client->templates->delete('template-123');
```

## API Key Management

```php
// Generate new API key (Owner/Admin only)
$result = $client->apiKeys->generate();
echo "New API key: {$result['api_key']}\n";
echo "⚠️  Store securely - shown only once!\n";

// Get API key info
$info = $client->apiKeys->info();
echo "Masked key: {$info['api_key_masked']}\n";
echo "Created: {$info['created_at']}\n";
echo "Last used: {$info['last_used_at']}\n";

// Regenerate API key
$result = $client->apiKeys->regenerate();
echo "New API key: {$result['api_key']}\n";

// Revoke API key
$client->apiKeys->revoke();
```

## Team Management

```php
// List team members
$team = $client->team->list();
echo "Active members: {$team['stats']['active_members']}\n";
echo "Pending invitations: {$team['stats']['pending_invitations']}\n";

// Check email before inviting
$check = $client->team->checkEmail('newmember@university.edu');
if ($check['recommendation'] === 'add_directly') {
    echo "User exists - can add directly\n";
} elseif ($check['recommendation'] === 'send_invitation') {
    echo "User doesn't exist - must send invitation\n";
}

// Invite team member
$result = $client->team->invite([
    'email' => 'newmember@university.edu',
    'role' => 'issuer',
    'send_email' => true
]);

// Update member role
$client->team->updateRole('member-123', 'admin');

// Deactivate member
$client->team->deactivate('member-123', 'Employee on leave');

// Reactivate member
$client->team->reactivate('member-123');

// Remove member permanently
$client->team->remove('member-123');
```

## Error Handling

```php
use DocWal\DocWalClient;
use DocWal\Exceptions\AuthenticationException;
use DocWal\Exceptions\ValidationException;
use DocWal\Exceptions\NotFoundException;
use DocWal\Exceptions\RateLimitException;
use DocWal\Exceptions\DocWalException;

$client = new DocWalClient('docwal_live_xxxxx');

try {
    $result = $client->credentials->issue([
        'template_id' => 'invalid-template',
        'individual_email' => 'student@example.com',
        'credential_data' => []
    ]);
} catch (AuthenticationException $e) {
    echo "Authentication failed: {$e->getMessage()}\n";
    echo "Check your API key\n";
} catch (ValidationException $e) {
    echo "Validation error: {$e->getMessage()}\n";
    echo "Check required fields\n";
} catch (NotFoundException $e) {
    echo "Resource not found: {$e->getMessage()}\n";
} catch (RateLimitException $e) {
    echo "Rate limit exceeded\n";
} catch (DocWalException $e) {
    echo "API error: {$e->getMessage()}\n";
    echo "Status code: {$e->getCode()}\n";
}
```

## Rate Limits

- **Pilot**: 500 requests/hour
- **Standard**: 1,000 requests/hour
- **Enterprise**: Unlimited

When rate limit is exceeded, `RateLimitException` is thrown.

## Laravel Integration Example

```php
<?php

namespace App\Http\Controllers;

use DocWal\DocWalClient;
use DocWal\Exceptions\DocWalException;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    private $docwal;

    public function __construct()
    {
        $this->docwal = new DocWalClient(config('services.docwal.api_key'));
    }

    public function issueCredential(Request $request)
    {
        try {
            $result = $this->docwal->credentials->issue([
                'template_id' => $request->input('template_id'),
                'individual_email' => $request->input('email'),
                'credential_data' => $request->input('data')
            ]);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (DocWalException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
```

### config/services.php
```php
return [
    // ... other services
    'docwal' => [
        'api_key' => env('DOCWAL_API_KEY'),
    ],
];
```

### .env
```
DOCWAL_API_KEY=docwal_live_xxxxx
```

## Support

- **Email**: support@docwal.com
- **Documentation**: https://docs.docwal.com
- **API Reference**: https://docwal.com/api/docs
- **GitHub**: https://github.com/docwal/docwal-php

## License

MIT License - see LICENSE file for details.
