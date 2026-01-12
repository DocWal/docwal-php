<?php
/**
 * Example: Issue a single credential using DocWal PHP SDK
 */

require_once __DIR__ . '/../vendor/autoload.php';

use DocWal\DocWalClient;
use DocWal\Exceptions\DocWalException;

// Initialize client
$client = new DocWalClient('your_api_key_here');

try {
    // Issue a credential
    $result = $client->credentials->issue([
        'template_id' => 'template-123',
        'individual_email' => 'student@example.com',
        'credential_data' => [
            'student_name' => 'John Doe',
            'degree' => 'Bachelor of Science',
            'major' => 'Computer Science',
            'graduation_date' => '2024-05-15',
            'gpa' => '3.8',
            'honors' => 'Cum Laude'
        ],
        'claim_token_expires_hours' => 720  // 30 days
    ]);

    echo "✅ Credential issued successfully!\n";
    echo "📄 Document ID: {$result['doc_id']}\n";
    echo "🔗 Document Hash: {$result['document_hash']}\n";
    echo "🎫 Claim Token: {$result['claim_token']}\n";
    echo "📧 Email sent to: student@example.com\n";

} catch (DocWalException $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Status Code: {$e->getCode()}\n";
}
