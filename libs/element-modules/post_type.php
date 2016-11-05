<?php

// Build Post Types
$usedfields = array();
$posttype_detect = "";
$post_type_render = "";
$posttype_messages = "";

// default labels
$defaultLabels = array(
	'name' 				=> '{{_name}}',
	'singular_name' 	=> '{{_singleName}}',
	'add_new' 			=> 'Add New',
	'add_new_item' 		=> 'Add New {{_singleName}}',
	'edit_item' 		=> 'Edit {{_singleName}}',
	'all_items' 		=> 'All {{_pluralName}}',
	'view_item' 		=> 'View {{_singleName}}',
	'search_items' 		=> 'Search {{_pluralName}}',
	'not_found' 		=> 'No {{_pluralName}} defined',
	'not_found_in_trash'=> 'No {{_pluralName}} in trash',
	'parent_item_colon' => '',
	'menu_name' 		=> '{{_pluralName}}',
);

if(!empty($export['post_type'])){

	$export_inits .= "add_action( 'init', array( \$this, 'activate_post_types' ) );\r\n";

	$post_type_render = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/posttype-render.php'));
	$posttype_detect = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/posttype-detector.php'));

	//$classfile = str_replace('{{init_post_types}}', "add_action( 'init', array( \$this, 'activate_post_types' ) );", $classfile);
	$posttypes = '';
	$posttype_methods = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/posttype.php'));
	$posttype_template = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/define-posttype.php'));	

	$posttype_block = null;
	$metabox_metaboxes = "";
	$posttype_list = "		\$this->elements = array_merge(\$this->elements, array(\r\n";
	$posttype_list .= "			'posttypes'			=>	array(\r\n";
	$posttype_metaboxes_block = "";
	
	foreach($export['post_type'] as $posttype){
		
		// get type define template
		$temp_block = $posttype_template;

		// setup labels
		foreach($defaultLabels as $label=>$defaultValue){
			if(isset($posttype['_post_type_label'][$label])){
				$defaultValue = $posttype['_post_type_label'][$label];
			}
			$temp_block = str_replace('{{'.$label.'}}', $defaultValue, $temp_block);
		}

		$posttype_messages .= str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/posttype-messages.php'));
		$posttype_list .= "				'".$posttype['_shortcode']."' 			=> '".(!empty($posttype['_browsable']) ? "browsable" : "element")."',\r\n";
		// view links
		if(!empty($posttype['_browsable'])){
			$posttype_messages = str_replace('{{_viewLinkInline}}', ' <a href="%s">\'.__(\'View {{_singleName}}\', \'{{slug}}\').\'</a>', $posttype_messages);
			$posttype_messages = str_replace('{{_viewLinkBlank}}', ' <a target="_blank" href="%s">Preview {{_singleName}}</a>', $posttype_messages);
			$posttype_messages = str_replace('{{_viewLinkBlankScheduled}}', ' <a target="_blank" href="%2$s">Preview {{_singleName}}</a>', $posttype_messages);
		}else{
			$posttype_messages = str_replace('{{_viewLinkInline}}', '', $posttype_messages);
			$posttype_messages = str_replace('{{_viewLinkBlank}}', '', $posttype_messages);
			$posttype_messages = str_replace('{{_viewLinkBlankScheduled}}', '', $posttype_messages);
		}	


		if(empty($posttype['_menuicon'])){
			$posttype['_menuicon'] = 'plugin_dir_url( __FILE__ ) . "assets/images/white.png"';
		}else{
			$icon = $posttype['_menuicon'];
			if(substr($posttype['_menuicon'], 0, 10) !== 'dashicons-'){
				$icon = 'dashicons-'.$posttype['_menuicon'];
			}
			$posttype['_menuicon'] = '"'.$icon.'"';//'plugin_dir_url( __FILE__ ) . "assets/images/'.basename($posttype['_menuicon']).'"';
		}
		$posttype['show_in_menu'] = "true";
		if(!empty($posttype['_showinmenu'])){
			//dump($posttype);
			//$posttype['_menuicon'] .=",\r\n			'show_in_menu'			=> '".$posttype['_showinmenu']."'";
			$posttype['show_in_menu'] = '"'.$posttype['_showinmenu'].'"';
		}
		if(!empty($posttype['_menuposition'])){
			$pos = floatval($posttype['_menuposition']);
		}else{
			$posttype['_menuposition'] = 'null';
		}
		// Setup post type support
		$supports = "'title',\r\n";
		if($posttype['_shortcodeType'] == 2){
			$supports .= "				'editor',\r\n";
		}				
		if(!empty($posttype['_support_author'])){
			$supports .= "				'author',\r\n";
		}
		if(!empty($posttype['_support_thumbnail'])){
			$supports .= "				'thumbnail',\r\n";
		}
		if(!empty($posttype['_support_excerpt'])){
			$supports .= "				'excerpt',\r\n";
		}
		if(!empty($posttype['_support_comments'])){
			$supports .= "				'comments',\r\n";
		}
		if(!empty($posttype['_browsable'])){
			$posttype['public'] = 'true';
			$posttype['publicly_queryable'] = 'true';
			$posttype['rewrite'] = 'true';
			//if(!empty($posttype['_taxonomies'])){
			//	$args['taxonomies'] = $posttype['_taxonomies'];
			//}
		}else{
			$posttype['public'] = 'false';
			$posttype['publicly_queryable'] = 'false';
			$posttype['rewrite'] = 'false';
		}
		$temp_block = str_replace('{{supports}}', $supports, $temp_block);
		// add meta box
		$metabox_methods = "";				
		$metabox_action = "";
		if(!empty($posttype['_variable'])){
			$metabox_action = "add_action('add_meta_boxes', array(\$this, 'add_metabox'), 5, 4);\r\n";
			$metabox_action .= "		add_action('save_post', array(\$this, 'save_post_metaboxes'), 1, 2);";

			$posttype_metaboxes_block = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/posttype-metabox.php'));
			//build fieldtypes
			//$metabox_groups = array();
			foreach($posttype['_groupvals'] as $group){
				$group_slug = sanitize_key($group);
				$varkeys = array_keys($posttype['_tabgroup'], $group);
				$metabox_metaboxes .= "add_meta_box('".$varkeys[0]."', __('".str_replace("'", "\'", ucwords( $group ) )."', '{{slug}}' ), array(\$this, 'render_metabox'), '".$posttype['_shortcode']."', 'normal'/* or side */, 'core', array( 'slug' => '". $posttype['_shortcode'] ."', 'file' => '". sanitize_file_name( strtolower( $group ) )."') );\r\n		";
				// add script and style includes
			}
			// add script and style includes
			foreach($posttype['_type'] as $type){
				$type = sanitize_key($type);
				if(!in_array($type, $usedfields)){
					if(!empty($fieldtype[$type]['scripts']) || !empty($fieldtype[$type]['styles'])){
						$adminstyles .= "\r\n		if(\$screen->post_type == '".$posttype['_shortcode']."' && \$screen->base == 'post'){\r\n";
						if(!empty($fieldtype[$type]['styles'])){
							foreach($fieldtype[$type]['styles'] as $style){
								$adminstyles .= "			wp_enqueue_style( \$this->plugin_slug . '-".$type."-styles-".str_replace('.', '-', $style)."', self::get_url( 'assets/css/".$style."', __FILE__ ), array(), self::VERSION );\r\n";
							}
						}
						if(!empty($fieldtype[$type]['scripts'])){
							foreach($fieldtype[$type]['scripts'] as $script){
								$adminstyles .= "			wp_enqueue_script( \$this->plugin_slug . '-".$type."-script-".str_replace('.', '-', $script)."', self::get_url( 'assets/js/".$script."', __FILE__ ), array( 'jquery' ), self::VERSION );\r\n";
							}
						}
						$adminstyles .= "		}\r\n		";
					}
					$usedfields[] = $type;
				}
			}

			$posttype_metaboxes_block = str_replace('{{add_meta_boxes}}', $metabox_metaboxes, $posttype_metaboxes_block);
			//dump($metabox_view);
			//$posttype['_type']
		}

		$temp_block = str_replace('{{add_metabox}}', "", $temp_block);
		foreach($posttype as $setting=>$value){
			if(is_array($value)){continue;}

			$value = str_replace("'", "\'", $value);
			
			$temp_block = str_replace('{{'.$setting.'}}', $value, $temp_block);
			$posttype_messages = str_replace('{{'.$setting.'}}', $value, $posttype_messages);
		}
		$posttype_block .= $temp_block;
	}
	// end off element list
	$posttype_list .= "			)\r\n";
	$posttype_list .= "		));\r\n";

	$posttype_block .= $posttype_list;

	//dump($usedfields);
	$posttype_methods = str_replace('{{post_type_metabox}}', $metabox_action, $posttype_methods);
	$posttype_methods = str_replace('{{meta_boxes}}', $posttype_metaboxes_block, $posttype_methods);			
	$posttype_methods = str_replace('{{register_post_types}}', $posttype_block, $posttype_methods);
	// widget or shortcode based post type
	$posttype_columns = "";
	$posttype_column_methods = "";
	if($posttype['_elementType'] != 6){
		$posttype_columns = "add_filter('manage_".$posttype['_shortcode']."_posts_columns', array(\$this, 'posts_column'),10,2);\r\n";
		$posttype_columns .= "		add_action('manage_".$posttype['_shortcode']."_posts_custom_column', array(\$this, 'custom_postcolumn'), 10, 2);";
		$posttype_column_methods = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/posttype-columns.php'));
	}
	$posttype_methods = str_replace('{{post_type_columns}}', $posttype_columns, $posttype_methods);
	$posttype_methods = str_replace('{{custom_columns}}', $posttype_column_methods, $posttype_methods);


	$classfile = str_replace('{{activate_post_types}}', $posttype_methods, $classfile);
}else{
	//$classfile = str_replace('{{init_post_types}}', "", $classfile);
	$classfile = str_replace('{{activate_post_types}}', "", $classfile);
}

$classfile = str_replace('{{post_messages}}', $posttype_messages, $classfile);
$classfile = str_replace('{{posttype_detector}}', $posttype_detect, $classfile);
//$classfile = str_replace('{{init_post_types}}', "add_action( 'init', array( \$this, 'activate_post_types' ) );", $classfile);
$classfile = str_replace('{{post_type_render_id}}', $post_type_render, $classfile);	