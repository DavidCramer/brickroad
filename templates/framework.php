<?php
/**
 * @package   {{_pluginClass}}
 * @author    {{_pluginAuthor}} <{{_pluginAuthorEmail}}>
 * @license   GPL-2.0+
 * @link      {{_pluginAuthorURI}}
 * @copyright {{_year}} {{_pluginAuthor}}
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( {{core_include_path}}/class-{{slug}}.php' );
{{functions}}
{{widgets}}{{settings}}


return {{_pluginClass}}::get_instance();
?>