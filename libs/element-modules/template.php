<?php

// Build template

$template_methods = null;
$template_list = null;
$post_type_detector = null;

if(!empty($export['template'])){	

	$template_items			= null;
	foreach($export['template'] as $template){
		
		if( empty( $template['_template_posttype'] ) ){
			continue;
		}

		if( $template['_template_depth'] == '1' ){
			$has_content = true;
			$template_items .= "{{template_types}}\r\n			return \$this->render_element(array(), \$content, '" . $template['_shortcode'] . "');\r\n		}\r\n		";
		}else{
			$has_full = true;
			$template_items .= "{{template_types}}\r\n			if( file_exists( self::get_path(__FILE__) . 'includes/element-" . $template['_shortcode'] . ".html') ){\r\n				return self::get_path(__FILE__) . 'includes/element-" . $template['_shortcode'] . ".html';\r\n			}elseif(  file_exists( self::get_path(__FILE__) . 'includes/element-" . $template['_shortcode'] . ".php') ){\r\n				return self::get_path(__FILE__) . 'includes/element-" . $template['_shortcode'] . ".php';\r\n			}\r\n		}\r\n		";
		}

		$template_post_types 	= array();

		foreach ($template['_template_posttype'] as $post_type) {
			$template_post_types[] = "'".$post_type."'";
		}
		
		if( $template['_template_type'] == '1' ){
			$check_type = " && is_single() ";
		}elseif( $template['_template_type'] == '2' ){
			$check_type = " && is_archive() ";
		}else{
			$check_type = null;
		}

		if(count($template_post_types) > 1){
			$post_type_templates = "if( in_array( \$post->post_type, array( " . implode( ',', $template_post_types ) . " ) )" . $check_type . " ){";
	
			// detector
			$post_type_detector .= "		// detect post type for headers\r\n		if( isset( \$wp_query->query['post_type'] ) ){\r\n			if( in_array( \$wp_query->query['post_type'], array( " . implode( ',', $template_post_types ) . " ) )" . $check_type . " ){\r\n			\$this->render_element(null, null, '" . $template['_shortcode'] . "', true);\r\n			}\r\n		}\r\n";

		}else{
			$post_type_templates = "if( \$post->post_type === " . $template_post_types[0] . $check_type . "){";
	
			// detector
			$post_type_detector .= "		// detect post type for headers\r\n		if( isset( \$wp_query->query['post_type'] ) ){\r\n			if( \$wp_query->query['post_type'] === " . $template_post_types[0] . $check_type . "){\r\n			\$this->render_element(null, null, '" . $template['_shortcode'] . "', true);\r\n			}\r\n		}\r\n";
		}
		
		// replace post type switch
		$template_items = str_replace('{{template_types}}', $post_type_templates, $template_items);		
	}

	if(!empty($has_content)){

		$export_inits .= "add_filter( 'the_content', array( \$this, 'use_content_template' ) );\r\n		";

		$template_methods .= str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/template-post-content-methods.php'));
		// add in template items
		$template_methods = str_replace('{{templates}}', $template_items, $template_methods);
	}

	if(!empty($has_full)){
		
		$export_inits .= "add_filter( 'template_include', array( \$this, 'use_post_template' ) );\r\n		";

		$template_methods .= str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/template-post-full-methods.php'));
		// add in template items
		$template_methods = str_replace('{{templates}}', $template_items, $template_methods);
	}

}

$classfile = str_replace('{{template_detector}}', $post_type_detector, $classfile);
$export_methods .= $template_methods;
