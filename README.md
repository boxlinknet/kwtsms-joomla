# kwtSMS for Joomla

A free Joomla extension that integrates the [kwtSMS](https://www.kwtsms.com) SMS gateway into your Joomla website. Send SMS notifications for orders, user registrations, OTP authentication, and more.

## Features

- A2P SMS notifications via kwtSMS REST/JSON API
- VirtueMart order notifications (new order, status updates)
- 6-tab admin panel: Dashboard, Settings, Templates, Integrations, Logs, Help
- Multilingual: English and Arabic (RTL)
- Daily auto-sync of balance, sender IDs, and coverage
- Full send pipeline: phone normalization, coverage check, message cleaning
- All sent messages logged locally with full API response
- Test mode support (test=1)

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

## Support

- Documentation: [www.kwtsms.com/support.html](https://www.kwtsms.com/support.html)

## License

GNU General Public License v2 or later. See [LICENSE](LICENSE).
