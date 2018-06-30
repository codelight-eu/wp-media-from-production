<?php
/**
 * Plugin Name: Media from Production
 * Plugin URI:  https://github.com/codelight-eu/media-from-production
 * Description: Uses local media when it's available, and uses the production server for rest.
 * Author:      Codelight, inspired by Bill Erickson
 * Author URI:  https://codelight.eu
 * Version:     1.0.0
 * Text Domain: codelight
 * Domain Path: languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

require_once('src/MediaFromProduction.php');

new Codelight\MediaFromProduction\MediaFromProduction();
