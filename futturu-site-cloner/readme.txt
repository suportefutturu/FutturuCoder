# Futturu Site Cloner

**Version:** 1.0.0  
**Author:** Futturu  
**License:** GPL v2 or later

## Description

Futturu Site Cloner is a WordPress plugin that provides a simplified graphical interface for wget, allowing web professionals to perform backup, mirroring, or cloning of static websites directly from the WordPress admin panel.

This tool is specifically designed for web professionals who need to quickly save content from clients' websites that are unavailable or experiencing hosting issues, avoiding hours of rework.

## Features

- **Simple Interface**: Easy-to-use form to input website URL and configure cloning options
- **Advanced Options**: 
  - Custom destination folder
  - Ignore specific file types (zip, mp4, etc.)
  - Configurable retry count and timeout
  - Option to ignore robots.txt
  - Custom User Agent
- **Real-time Logs**: View wget command execution status and progress
- **Backup Management**: List, download (as ZIP), and delete cloned sites
- **Security**: 
  - All backups stored in protected directory
  - .htaccess protection against direct browser access
  - Input sanitization and validation
  - Administrator-only access

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- wget installed on the server
- PHP ZipArchive extension enabled
- Server must allow PHP to execute shell commands

## Installation

1. Upload the `futturu-site-cloner` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to 'Futturu Tools > Clone Site' to start using the plugin

## Usage

### Basic Usage

1. Go to **Futturu Tools > Clone Site** in your WordPress admin
2. Enter the full URL of the website you want to clone (must include http:// or https://)
3. Optionally, provide a custom backup name
4. Click **Clone Site**
5. Monitor the real-time log to see the cloning progress
6. Once completed, go to **Backups** page to download or manage your backups

### Advanced Options

Click on "Advanced Options" to reveal additional settings:

- **Ignore File Extensions**: Comma-separated list of file extensions to exclude (e.g., zip,mp4,avi)
- **Max Tries**: Number of retries for failed downloads (default: 10)
- **Timeout**: Timeout in seconds for network operations (default: 30)
- **User Agent**: Custom User Agent string to send with requests
- **Ignore robots.txt**: Check this to ignore robots.txt restrictions

### Managing Backups

Navigate to **Futturu Tools > Backups** to:

- View all cloned sites with their details (name, URL, date, size, status)
- Download any backup as a ZIP file
- Delete unwanted backups

## Example wget Command

The plugin generates wget commands similar to this:

```bash
wget --mirror --convert-links --adjust-extension --page-requisites --no-parent \
     -e robots=off -U "Mozilla/5.0" --tries=10 --timeout=30 \
     -P "/wp-content/futturu-backups/example-com/" \
     "https://example.com/"
```

### Parameter Explanation

| Parameter | Description |
|-----------|-------------|
| `--mirror` | Enables recursion and infinite depth for complete site mirroring |
| `--convert-links` | Converts links to work locally after download |
| `--adjust-extension` | Saves files with appropriate extensions (.html, etc.) |
| `--page-requisites` | Downloads all necessary files (CSS, images, JS) for proper display |
| `--no-parent` | Prevents ascending to parent directories during recursion |
| `-e robots=off` | Ignores robots.txt restrictions |
| `-U "Mozilla/5.0"` | Sets a custom User Agent string |
| `--tries=N` | Number of retries for failed downloads |
| `--timeout=N` | Timeout in seconds for network operations |
| `-P` | Directory prefix where files will be saved |

## Security Considerations

- All backups are stored in `/wp-content/futturu-backups/`, which is protected by:
  - `.htaccess` file denying direct browser access
  - `index.php` file preventing directory listing
- Only users with administrator privileges can access the plugin
- All user inputs are sanitized and validated before use
- wget commands use strict parameter escaping to prevent command injection

## Limitations

- **Dynamic Content**: Works best with static websites. Sites heavily reliant on JavaScript, databases, or server-side processing may not clone perfectly.
- **Authentication**: Cannot clone sites requiring login credentials.
- **Large Sites**: Very large websites may consume significant time and server resources.
- **Server Requirements**: Requires wget to be installed and PHP to have permission to execute shell commands.

## Troubleshooting

### wget command not found
Ensure wget is installed on your server. Contact your hosting provider if needed.

### Permission denied errors
Check that the plugin has write permissions to create directories in `/wp-content/`.

### Timeout errors
Increase the timeout value in Advanced Options for larger sites.

### Incomplete clones
Some sites may have anti-scraping measures. Try adjusting the User Agent or increasing retry count.

## Support

For support and updates, visit: [https://futturu.com.br](https://futturu.com.br)

## Changelog

### Version 1.0.0
- Initial release
- Basic site cloning functionality
- Advanced configuration options
- Backup management (list, download, delete)
- Real-time logging
- Help documentation integrated in admin

## License

This plugin is licensed under GPL v2 or later.

```
Copyright (C) 2024 Futturu

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
```
