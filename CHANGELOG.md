# Changelog

All notable changes to the DocWal PHP SDK will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-10

### Added
- Initial release of DocWal PHP SDK
- PSR-4 autoloading compliant structure
- Credentials resource with full CRUD operations
- Template management (create, list, update, delete)
- Team member management (invite, list, update roles, deactivate, remove)
- API key management (generate, regenerate, revoke, info)
- Batch credential issuance (JSON array and ZIP upload)
- File upload support for PDF documents
- Comprehensive error handling with custom exception classes
- Full documentation with PHP examples
- Laravel integration example
- Guzzle HTTP client integration

### Features
- Issue single credentials with optional PDF attachments
- Batch issue up to 1000 credentials at once
- Batch upload with ZIP files (CSV/JSON + PDFs)
- List and filter credentials with pagination
- Revoke credentials with audit trail
- Resend claim links with configurable expiration
- Download credential files
- Manage credential templates with schema validation
- Team collaboration with role-based permissions
- Secure API key authentication
- Resource-based architecture

### PHP Compatibility
- PHP 7.4+ support
- PSR-4 autoloading
- Composer package management
- Namespace: `DocWal\`

### Security
- API key-based authentication
- HTTPS-only communication
- Input validation for all requests
- Proper error handling without leaking sensitive data
