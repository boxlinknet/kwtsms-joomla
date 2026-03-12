# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability in the kwtSMS Joomla Extension, please report it privately.

**Do not open a public GitHub issue for security vulnerabilities.**

Send a report to: **support@kwtsms.com**

Include in your report:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

We aim to respond within 48 hours and will work to release a fix as quickly as possible.

## Supported Versions

| Version | Supported |
|---------|-----------|
| 1.x     | Yes       |

## Security Practices

This extension follows these security practices:

- API credentials stored encrypted (AES-256-CBC) in the database
- All credentials masked in log output
- All database queries use Joomla's parameterized query builder (no raw SQL)
- All user input sanitized via Joomla's InputFilter
- All output escaped to prevent XSS
- HTTPS POST only for all API calls (never GET)
- Generic error messages to prevent information leakage
