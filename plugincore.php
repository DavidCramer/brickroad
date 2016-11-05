<?php
/*
  Plugin Name: Brickroad
  Plugin URI: http://cramer.co.za
  Description: Build advanced custom plugins, widgets shortcodes and web elements to use within your site or distribute to others as standalone plugins or included in your themes.
  Author: David Cramer
  Version: 1.0.0
  Author URI: http://digilab.co.za
 */

//initilize plugin
define('BRICKROAD_PATH', plugin_dir_path(__FILE__));
define('BRICKROAD_URL', plugin_dir_url(__FILE__));
define('BRICKROAD_VER', 'alpha');
define('BRICKROAD_DOCS', 'https://cramer.co.za');
define('BRICKROAD_NAME', 'Brickroad');

if(is_admin()){
  require_once BRICKROAD_PATH . 'libs/exporter.php';
	require_once BRICKROAD_PATH . 'libs/admin-functions.php';
}
require_once BRICKROAD_PATH . 'libs/functions.php';
require_once BRICKROAD_PATH . 'libs/actions.php';
