<?php

// setup bases
$shortcode_actions = "";
$shortcode_insert_method = "";
$shortcode_definition = "";
$shortcode_modaljs = "";
$usedfields = array();
$shortcode_selector = array(
	'__all__'	=> ""
);
$shortcode_detect = "";
$general_methods = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/general-methods.php'));
$shortcode_headers = null;
$shortcode_simple = null;
if(!empty($export['shortcode'])){

	// put in the shortcode detector
	$shortcode_detect = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/shortcode-detector.php'));
	// Put in the modal panel
	$shortcode_modaljs = $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/scripts/shortcode-modal.js');
	$adminstyles .= "\r\n		if(\$screen->base == 'post'){\r\n";
	// modalscripts
	$adminstyles .= "			wp_enqueue_script( \$this->plugin_slug . '-shortcode-modal-script', self::get_url( 'assets/js/shortcode-modal.js', __FILE__ ), array( 'jquery' ), self::VERSION );\r\n";
	$adminstyles .= "			wp_enqueue_script( \$this->plugin_slug . '-panel-script', self::get_url( 'assets/js/panel.js', __FILE__ ), array( 'jquery' ), self::VERSION );\r\n";
	$adminstyles .= "			wp_enqueue_style( \$this->plugin_slug . '-panel-styles', self::get_url( 'assets/css/panel.css', __FILE__ ), array(), self::VERSION );\r\n";

	if(!empty($fieldtype[$type]['styles'])){
		foreach($fieldtype[$type]['styles'] as $style){

			$adminstyles .= "			wp_enqueue_style( \$this->plugin_slug . '-".$type."-styles-".str_replace('.', '-', $style)."', self::get_url( 'assets/css/".$style."', __FILE__ ), array(), self::VERSION );\r\n";
		}
	}

	$shortcode_actions = "if(is_admin()){\r\n";
	$shortcode_actions .= "			add_action( 'media_buttons', array(\$this, 'shortcode_insert_button' ), 11 );\r\n";
	$shortcode_actions .= "			add_action( 'admin_footer', array( \$this, 'shortcode_modal_template' ) );\r\n";
	$shortcode_actions .= "		}\r\n";
	$shortcode_insert_method = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/shortcode-methods.php'));
	$shortcode_definition .= "// Add shortcodes\r\n";
	$shortcode_list = "		\$this->elements = array_merge(\$this->elements, array(\r\n";
	$shortcode_list .= "			'shortcodes'			=>	array(\r\n";
	if(count($export['shortcode']) > 1){
		if(count($categories) > 1){
			// make the category selector
			$category_list = "";

			$shortcode_selector['__all__'] .= __("Category","{{slug}}").": <span id=\\\"{{slug}}-categories\\\">";
			$shortcode_selector['__all__'] .= "<select id=\\\"{{slug}}-category-selector\\\">";
			$shortcode_selector['__all__'] .= "<option value=\\\"\\\"></option>";
			foreach($categories as $catkey=>$category){
				if(!isset($shortcode_selector[$catkey])){
					$shortcode_selector[$catkey] = "<select style=\\\"display:none;\\\" class=\\\"{{slug}}-elements-selector\\\" id=\\\"{{slug}}-elements-selector-".$catkey."\\\">";
					$shortcode_selector[$catkey] .= "				<option value=\\\"\\\"></option>";
				}
				$shortcode_selector['__all__'] .= "<option value=\\\"".$catkey."\\\">".$category."</option>";
			}
			$shortcode_selector['__all__'] .= "</select>";
			$shortcode_selector['__all__'] .= "</span>&nbsp;";
			$shortcode_selector['__all__'] .= __("Shortcode","{{slug}}").": <span id=\\\"{{slug}}-elements\\\">";
			$shortcode_selector['__all__'] .= "<span class=\\\"{{slug}}-elements-selector\\\" id=\\\"{{slug}}-elements-selector\\\">".__("Select Category","{{slug}}")."</span>";
		}else{
			$shortcode_selector['__all__'] .= __("Shortcode","{{slug}}").": <span id=\\\"{{slug}}-elements\\\">";
			$shortcode_selector['__all__'] .= "<select class=\\\"{{slug}}-elements-selector\\\" id=\\\"{{slug}}-elements-selector\\\">";
			$shortcode_selector['__all__'] .= "<option value=\\\"\\\">".__("Select Shortcode","{{slug}}")."</option>";
		}
	}else{
		//if($export['shortcode'])
		$firstitem = array_keys($export['shortcode']);
		if(empty($export['shortcode'][$firstitem[0]]['_variable']) && empty($export['shortcode'][$firstitem[0]]['_posttype'])){
			$shortcode_simple = ' data-shortcode=\"'.$export['shortcode'][$firstitem[0]]['_shortcode'].'\"';
		}
		
			//$general_methods
	}

	foreach($export['shortcode'] as $shortcode){
		// add to list
		$shortcode_list .= "				'".$shortcode['_shortcode']."' 			=> '".$shortcode['_shortcodeType']."',\r\n";
		$shortcode_definition .= "		add_shortcode('".$shortcode['_shortcode']."', array(\$this, 'render_element'));\r\n";
		if(count($export['shortcode']) > 1){
			$subcat = '__all__';
			if(count($categories) > 1){
				$subcat = sanitize_key($shortcode['_category']);
			}
			if(count($export['shortcode']) > 1){
				$shortcode_selector[$subcat] .= "<option value=\\\"".$shortcode['_shortcode']."\\\">\".__('". str_replace("'", "\'", $shortcode['_name']) ."','{{slug}}').\"</option>\r\n";
			}
		}else{
			$shortcode_selector['__all__'] = "<div class=\\\"{{slug}}-shortcode-name\\\">\".__('". str_replace("'", "\'", $shortcode['_name']) ."','{{slug}}').\"</div><span class=\\\"{{slug}}-autoload\\\" data-shortcode=\\\"".$shortcode['_shortcode']."\\\"></span>";
		}
		// export headers -css
		if(!empty($shortcode['_cssLib'])){
			// enqueue scripts
			foreach($shortcode['_cssLib'] as $csskey=>$src){
				if(!empty($shortcode['_assetLabel'])){
					if( in_array($src, $shortcode['_assetLabel']) ){
						$assetslug = array_search($src, $shortcode['_assetLabel']);
						$shortcode_headers .= "					wp_enqueue_style( '".$shortcode['_shortcode']."-".$src."', self::get_url( '".$shortcode['_assetURL'][$assetslug]."', __FILE__ ) );\r\n";
					}else{
						if(false !== strpos($src, '/')){
							$shortcode_headers .= "					wp_enqueue_style( '".$shortcode['_shortcode']."-".sanitize_key(basename($src))."', '".$src."' );\r\n";
						}else{
							$shortcode_headers .= "					wp_enqueue_style( '". strtolower( $src ) ."' );\r\n";
						}
					}
				}else{
					if(false !== strpos($src, '/')){
						$shortcode_headers .= "						wp_enqueue_style( '".$shortcode['_shortcode']."-".sanitize_key(basename($src))."', '".$src."' );\r\n";
					}else{
						$shortcode_headers .= "						wp_enqueue_style( '" . strtolower( $src ) . "' );\r\n";
					}
				}
			}
		}
		//libs
		// export headers -js
		if(!empty($shortcode['_jsLib'])){
			// enqueue scripts
			foreach($shortcode['_jsLib'] as $jskey=>$src){
				if(!empty($shortcode['_assetLabel'])){
					if( in_array($src, $shortcode['_assetLabel']) ){
						$assetslug = array_search($src, $shortcode['_assetLabel']);
						$shortcode_headers .= "					wp_enqueue_script( '".$shortcode['_shortcode']."-".$src."', self::get_url( '".$shortcode['_assetURL'][$assetslug]."', __FILE__ ), array( 'jquery' ) , false, ".($shortcode['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );\r\n";
					}else{
						if(false !== strpos($src, '/')){
							$shortcode_headers .= "					wp_enqueue_script( '".$shortcode['_shortcode']."-".sanitize_key(basename($src))."', '".$src."', false , false, ".($shortcode['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );\r\n";
						}else{
							$shortcode_headers .= "					wp_enqueue_script( '". strtolower( $src ) ."', false, false , false, ".($shortcode['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );\r\n";
						}
					}
				}else{
					if(false !== strpos($src, '/')){
						$shortcode_headers .= "					wp_enqueue_script( '".$shortcode['_shortcode']."-".sanitize_key(basename($src))."', '".$src."', false , false, ".($shortcode['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );\r\n";
					}else{
						$shortcode_headers .= "					wp_enqueue_script( '". strtolower( $src ) ."', false, false , false, ".($shortcode['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );\r\n";
					}
				}
			}
		}		


		if(!empty($shortcode['_variable'])){
			// add script and style includes
			foreach($shortcode['_type'] as $type){
				$type = sanitize_key($type);
				if(!in_array($type, $usedfields)){
					if(!empty($fieldtype[$type]['scripts']) || !empty($fieldtype[$type]['styles'])){

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
					}
					$usedfields[] = $type;
				}
			}
		}
	}
	// replaces of headers
	$shortcode_detect = str_replace('{{shortcodeheads}}', $shortcode_headers, $shortcode_detect);
	// end off element list
	$shortcode_list .= "			)\r\n";
	$shortcode_list .= "		));\r\n";
	if(count($export['shortcode']) > 1){
		if(count($categories) > 1){
			$shortcode_selector['__all__'] .= "</span>\r\n";
			foreach($categories as $catkey=>$category){
				$shortcode_selector[$catkey] .= "</select>\r\n</span>\r\n";
			}
		}else{
			$shortcode_selector['__all__'] .= "</select>\r\n</span>\r\n";
		}
	}
	// add shortcode assets
	$paneljs = $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/scripts/panel.js');
	$wp_filesystem->put_contents($plugin_path . '/assets/js/panel.js', $paneljs, FS_CHMOD_FILE);

	//end off enqueue closure
	$adminstyles .= "		}\r\n		";

	//clean up js and write
	foreach($plugin as $variable=>$setting){
		$paneljs = str_replace('{{'.$variable.'}}', $setting, $paneljs);
		$shortcode_modaljs = str_replace('{{'.$variable.'}}', $setting, $shortcode_modaljs);
	}

	$shortcode_definition .= $shortcode_list;

	//clean up js and write
	$wp_filesystem->put_contents($plugin_path . '/assets/js/panel.js', $paneljs, FS_CHMOD_FILE);
	$wp_filesystem->put_contents($plugin_path . '/assets/js/shortcode-modal.js', $shortcode_modaljs, FS_CHMOD_FILE);

}
// shortcode detector
$classfile = str_replace('{{shortcode_detect}}', $shortcode_detect, $classfile);
//$classfile = str_replace('{{shortcode_actions}}', $shortcode_actions, $classfile);
$export_inits .= $shortcode_actions;
$export_inits .= $shortcode_definition;

$shortcode_insert_method = str_replace('{{is_single_clean}}', $shortcode_simple, $shortcode_insert_method);

$shortcode_insert_method = str_replace('{{shortcode_selector}}', implode(' ', $shortcode_selector), $shortcode_insert_method);

$export_methods .= $shortcode_insert_method;

//$classfile = str_replace('{{shortcode_methods}}', $shortcode_insert_method, $classfile);
//$classfile = str_replace('{{declare_shortcode}}', $shortcode_definition, $classfile);
