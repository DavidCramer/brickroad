<?php

/*
 * CUSTOM ELEMENTS actions library
 * (C) 2014 - David Cramer
 */

add_action('wp_loaded', 'brickroad_init');

/*
** Load Scripts / Styles
*/
add_action('admin_enqueue_scripts'  , 'brickroad_enqueue_scripts_styles', 10, 1);


add_action('admin_menu', 'brickroad_menus');
// Pull in admin processes
if(is_admin()){
	add_action('admin_menu', 'brickroad_admin_processes');
}

// ADMIN AJAX
if (is_admin() === true) {

	add_action('wp_ajax_docpage', 'brickroad_docpage');
	add_action('wp_ajax_docmenu', 'brickroad_docsloader');

	add_action('wp_ajax_brickroad-preview', 'brickroad_loadPreview');
	
	add_action('wp_ajax_delete_element', 'brickroad_deleteElement');
	add_action('wp_ajax_apply_element', 'brickroad_applyElement');
	add_action('wp_ajax_load_elements', 'brickroad_loadElements');
	add_action('wp_ajax_move_element', 'brickroad_moveElement');
	add_action('wp_ajax_set_tooltips', 'brickroad_setTooltips');
	add_action('wp_ajax_brickroad_dismisssavepointer', 'brickroad_dismisssavepointer');
	add_action('wp_ajax_upgrade_elements', 'brickroad_upgradeElements');
	add_action('admin_footer', 'brickroad_ajax_javascript');
}
