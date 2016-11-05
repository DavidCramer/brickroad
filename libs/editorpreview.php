<?php
if(!empty($_POST['formData'])){
	parse_str( $_POST['formData'], $element);
	$element = $element['data'];
}elseif ( !empty($_GET['code'])){
	$element = get_option($_GET['code']);
	include ABSPATH . 'wp-admin/admin.php';
}else{
	echo '<div class="message error">Please apply element before loading a preview.</div>';
	die;
}

$preview_slug = sanitize_title( $element['_ID'] );
$plugin_path = BRICKROAD_PATH . 'elements/' . $preview_slug;
include $plugin_path . '/preview.php';

$instance = $element['_ID']::get_instance();
$instance->enqueue_admin_stylescripts();

if( !empty( $_POST['preview'] ) ){
	
	$plugin_url = BRICKROAD_URL . 'elements/' . $preview_slug;

	$attribute_groups = array();
	if(!empty($element['_groupvals'])){
		foreach($element['_groupvals'] as $group){
			$attribute_groups[] = sanitize_file_name($group);
		}
	}
	

	
	$out = $instance->render_attributes_panel($element['_shortcode'], $attribute_groups);
	if(empty($out['html'])){
		$out['html'] = '<div class="error" style="display:block;"><p>You dont have any attributes setup.</p></div>';
	}else{
		$out['html'] .= '<div><button type="button" class="button-primary save-att-config" style="margin-left: 136px; margin-top: 20px;">Save Attributes</a></div>';
	}
	$out['style']['panel_css'] = $plugin_url . '/assets/css/panel.css';
	$out['script']['panel_js'] = $plugin_url . '/assets/js/panel.js';

	header('Content-Type: application/json');
	echo json_encode( $out );
	die;
}



//die;
?><!DOCTYPE html>
<html>
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
	<title>Element Preview</title>       
	<?php wp_head(); ?>
</head>
<body class="previewCanvas">
	<?php wp_footer(); ?>
</body>
</html>