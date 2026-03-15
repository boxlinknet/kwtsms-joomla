# kwtSMS for Joomla

[![CI](https://github.com/boxlinknet/kwtsms-joomla/actions/workflows/ci.yml/badge.svg)](https://github.com/boxlinknet/kwtsms-joomla/actions/workflows/ci.yml)
[![Latest Release](https://img.shields.io/github/v/release/boxlinknet/kwtsms-joomla)](https://github.com/boxlinknet/kwtsms-joomla/releases/latest)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Joomla](https://img.shields.io/badge/Joomla-4%2B-1A3867?logo=joomla&logoColor=white)](https://www.joomla.org)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-blue)](LICENSE)

A free Joomla extension that integrates the [kwtSMS](https://www.kwtsms.com) SMS gateway into your Joomla website. Send SMS notifications for orders, user registrations, OTP authentication, and more.

> Don't have a kwtSMS account? [Sign up at kwtsms.com](https://www.kwtsms.com/signup)

---

## About kwtSMS

kwtSMS is a Kuwaiti SMS gateway trusted by top businesses to deliver messages anywhere in the world, with private Sender ID, free API testing, non-expiring credits, and competitive flat-rate pricing. Secure, simple to integrate, built to last. Open a free account in under 1 minute, no paperwork or payment required. [Get started](https://www.kwtsms.com/signup/)

---

## Features

- A2P SMS notifications via kwtSMS REST/JSON API
- VirtueMart order notifications (new order, status updates)
- 6-tab admin panel: Dashboard, Settings, Templates, Integrations, Logs, Help
- Multilingual: English and Arabic
- Daily auto-sync of balance, sender IDs, and coverage
- Full send pipeline: phone normalization, coverage check, message cleaning
- All sent messages logged locally with full API response
- Test mode: queue messages without delivery for safe development

## Requirements

- Joomla 4.0 or higher (tested on Joomla 5/6)
- PHP 8.1 or higher
- MySQL 8.0 or higher
- VirtueMart 4.x (for order notifications)
- kwtSMS account with API access: [www.kwtsms.com](https://www.kwtsms.com)

## Installation

1. Download `pkg_kwtsms.zip` from the [Joomla Extensions Directory](https://extensions.joomla.org)
2. In Joomla admin: Extensions > Install > Upload Package File
3. Upload `pkg_kwtsms.zip` and click Install
4. Go to Components > kwtSMS > Settings
5. Enter your kwtSMS API username and password
6. Click "Test Connection" to verify and save credentials
7. Enable the gateway and configure your sender ID
8. Enable the system, task, and VirtueMart plugins under Extensions > Plugins

## Configuration

See the Help tab in the kwtSMS admin panel for a full setup guide.

## API Credentials

Get your API credentials from your kwtSMS account at [www.kwtsms.com](https://www.kwtsms.com):

1. Log in to your kwtSMS account
2. Go to Account > API
3. Enable API access and note your API username and password
4. Do not use your mobile number as the username

## Help & Support

- [kwtSMS FAQ](https://www.kwtsms.com/faq/): Answers to common questions about credits, sender IDs, OTP, and delivery.
- [kwtSMS Support](https://www.kwtsms.com/support.html): Open a support ticket or browse help articles.
- [API Documentation (PDF)](https://www.kwtsms.com/doc/KwtSMS.com_API_Documentation_v41.pdf): kwtSMS REST API v4.1 full reference.
- [Sender ID Help](https://www.kwtsms.com/sender-id-help.html): Sender ID registration and guidelines.
- [kwtSMS Dashboard](https://www.kwtsms.com/login/): Recharge credits, buy Sender IDs, view message logs, and manage coverage.
- [Other Integrations](https://www.kwtsms.com/integrations.html): Plugins and integrations for other platforms and languages.
- [Plugin Issues](https://github.com/boxlinknet/kwtsms-joomla/issues): Report bugs or request features.

## License

GNU General Public License v2 or later. See [LICENSE](LICENSE).
