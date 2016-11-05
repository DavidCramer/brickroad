<?php

// Build Dashboard

$dashboard_render = null;

if(!empty($export['dashboard'])){
	$dashboard_render = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/dashboard-methods.php'));
	foreach($export['dashboard'] as $dashboard){
		
	}
}
$export_methods .= $dashboard_render;