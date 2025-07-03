# Intrada Elementor Form Capture

A WordPress plugin that captures Elementor form submissions and sends them to a custom endpoint.

## Description

This plugin integrates with Elementor Pro forms to capture form submissions and forward them to custom endpoints for processing. It provides a seamless way to handle form data from Elementor forms in your custom applications.

## Features

- Captures Elementor Pro form submissions
- Sends form data to custom endpoints
- Easy configuration through WordPress admin
- Robust error handling and logging

## Requirements

- WordPress 5.0 or higher
- Elementor Pro plugin
- PHP 7.4 or higher

## Installation

1. Download the latest release from the [Releases page](../../releases)
2. Upload the plugin to your WordPress site
3. Activate the plugin through the WordPress admin
4. Configure the settings as needed

## Development

### Automatic Releases

This plugin uses GitHub Actions to automatically create releases when code is merged to the main branch. The workflow:

1. Extracts the version number from the main plugin file
2. Creates a ZIP archive of the plugin
3. Generates a changelog from commit messages
4. Creates a GitHub release with the ZIP file attached

To trigger a new release, simply update the version number in `intrada-elementor-form-capture.php` and merge to main.


## Updates

This plugin knows to check github for udpates - this requires the info.json to be updated when you update the plugin.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Author

**Intrada Technologies**  
Website: [https://intradatech.com/](https://intradatech.com/)