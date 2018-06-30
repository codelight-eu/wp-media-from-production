<?php
/**
 * Plugin Name: Media from Production
 * Plugin URI:  https://github.com/codelight-eu/media-from-production
 * Description: Uses local media when it's available, and uses the production server for rest. Fork of <a target="_blank" href="https://github.com/billerickson/BE-Media-from-Production">Bill Erickson's plugin</a>.
 * Author:      Codelight
 * Author URI:  https://codelight.eu
 * Version:     1.0.0
 * Text Domain: codelight
 * Domain Path: languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

require_once('src/MediaFromProduction.php');

/**
 * Run the plugin only if WP_ENV is defined and it's not production
 */
if (defined('WP_ENV') && 'production' !== WP_ENV) {
    new Codelight\MediaFromProduction\MediaFromProduction();
}

