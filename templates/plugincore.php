<?php
/**
 * @package   {{_pluginClass}}
 * @author    {{_pluginAuthor}} <{{_pluginAuthorEmail}}>
 * @license   GPL-2.0+
 * @link      {{_pluginAuthorURI}}
 * @copyright {{_year}} {{_pluginAuthor}}
 *
 * @wordpress-plugin
 * Plugin Name: {{_pluginName}}
 * Plugin URI:  {{_pluginURI}}
 * Description: {{_pluginDescription}}
 * Version:     {{_pluginVersion}}
 * Author:      {{_pluginAuthor}}
 * Author URI:  {{_pluginAuthorURI}}
 * Text Domain: {{slug}}
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( {{core_include_path}}class-{{slug}}.php' );
{{functions}}
{{widgets}}{{settings}}
// Register hooks that are fired when the plugin is activated or deactivated.
// When the plugin is deleted, the uninstall.php file is loaded.
register_activation_hook( __FILE__, array( '{{_pluginClass}}', 'activate' ) );
register_deactivation_hook( __FILE__, array( '{{_pluginClass}}', 'deactivate' ) );

// Load instance
add_action( 'plugins_loaded', array( '{{_pluginClass}}', 'get_instance' ) );
