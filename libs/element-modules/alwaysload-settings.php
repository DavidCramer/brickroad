<?php

$settings_init = "";
$settings_template = "";
$alwaysload_detect = "";
$alwaysload_headers = "";
$alwaysload_headers_settings = "";
$alwaysload_actions = "";
$alwaysload_headtemplate = "";
$alwaysload_foottemplate = "";
$alwaysload_contenttemplate = "";
$alwaysload_methods = "";
$settings_items = null;
$settings_register = "";

$hasVars = false;
if(!empty($export['alwaysload']) || !empty($export['settings'])){

	$settings_elements = array();
	if(!empty($export['alwaysload'])){
		$settings_elements = array_merge($settings_elements, $export['alwaysload']);
	}
	if(!empty($export['settings'])){
		$settings_elements = array_merge($settings_elements, $export['settings']);
	}

	foreach($settings_elements as $settingsSlug=>$settings){

		$settings_item_parent_template = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/settings-item-parent.php'));
		$settings_item_template = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/settings-item.php'));
		$settings_page_template = $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/settings-page.php');


		if(!empty($settings['_variable']) || $settings['_settings_build'] == 'template'){
			$hasVars = true;
			$settings_init .= "require_once( {{core_include_path}}/includes/settings.php' );\r\n";
			$settings_template = $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/settings.php');

			if($settings['_settings_build_struct'] == 'auto'){
				unset($settings['_variable']);
				$settings_template = str_replace( '{{build_structures}}', "include self::get_path( __FILE__ ) . 'settings-' . \$base . '.php';\r\n", $settings_template);
			}else{
				$settings_template = str_replace( '{{build_structures}}', "", $settings_template);
			}
			
			break;
		}
	}	

	//if(!empty($hasVars)){
		foreach($settings_elements as $settingsSlug=>$settings){
			if(!isset($settings['_settingsParent']))
				$settings['_settingsParent'] = null;
			
			if(!isset($settings['_settingsIcon']))
				$settings['_settingsIcon'] = null;
			
			if($settings['_settingsParent'] == 'parent'){
				$settings_item = $settings_item_parent_template;
			}else{
				$settings_item = $settings_item_template;
			}
			$settings_item = str_replace('{{_settingsSlug}}', $settingsSlug, $settings_item);
			$settings_item = str_replace('{{_pageTitle}}', str_replace("'", "\'", $settings['_name'] ), $settings_item);
			$settings_item = str_replace('{{_menuLabel}}', $settings['_name'], $settings_item);
			if($settings['_settingsParent'] == 'custom'){
				$settings_item = str_replace('{{_settingsParent}}', $settings['_customLocation'], $settings_item);
			}else{
				$settings_item = str_replace('{{_settingsIcon}}', $settings['_settingsIcon'], $settings_item);
				$settings_item = str_replace('{{_settingsParent}}', $settings['_settingsParent'], $settings_item);
			}

			
			$settings_items .= $settings_item;
			// pages
			$settings_page = $settings_page_template;
			$settings_page = str_replace('{{_settingsSlug}}', $settingsSlug, $settings_page);
			$settings_page = str_replace('{{_pageTitle}}', str_replace("'", "\'", $settings['_name'] ), $settings_page);
			$settings_page = str_replace('{{description}}', ($settings['_description'] ? '<p><?php echo __("' . str_replace('"', '\"', $settings['_description'] ) . '","{{slug}}"); ?></p>' : '') , $settings_page);

			foreach($plugin as $variable=>$setting){
				$settings_page = str_replace('{{'.$variable.'}}', $setting, $settings_page);
			}				
			$wp_filesystem->put_contents( $plugin_path . '/includes/settings-'.$settingsSlug.'.php', $settings_page, FS_CHMOD_FILE);
			
			$settings_register .= "register_setting(\r\n";
			$settings_register .= "			'".$settingsSlug."',\r\n";
			$settings_register .= "			'_".$settingsSlug."_options'\r\n";
			//$settings_register .= array( $this, 'sanitize' ) // Sanitize
			$settings_register .= "		);\r\n";


			$alwaysload_detect .= "\r\n		\$this->render_element(get_option( \"_".$settings['_shortcode']."_options\" ), false, '".$settings['_shortcode']."', true);\r\n";
			// export headers -css
			if(!empty($settings['_cssLib'])){
				// enqueue scripts
				foreach($settings['_cssLib'] as $csskey=>$src){
					if(!empty($settings['_assetLabel'])){
						if( in_array($src, $settings['_assetLabel']) ){
							$assetslug = array_search($src, $settings['_assetLabel']);
							$alwaysload_headers .= "\r\n			wp_enqueue_style( '{{_alwaysloadSlug}}-".$src."', self::get_url( '".$settings['_assetURL'][$assetslug]."', __FILE__ ) );";
						}else{
							if(false !== strpos($src, '/')){
								$alwaysload_headers .= "\r\n			wp_enqueue_style( '{{_alwaysloadSlug}}-".sanitize_key( $src )."', '".$src."' );";
							}else{
								$alwaysload_headers .= "\r\n			wp_enqueue_style( '".$src."' );";
							}
						}
					}else{
						if(false !== strpos($src, '/')){
							$alwaysload_headers .= "\r\n			wp_enqueue_style( '{{_alwaysloadSlug}}-".sanitize_key( $src )."', '".$src."' );";
						}else{
							$alwaysload_headers .= "\r\n			wp_enqueue_style( '".$src."' );";
						}
					}
				}
			}
			// export headers -js

			if(!empty($settings['_jsLib'])){
				// enqueue scripts
				foreach($settings['_jsLib'] as $jskey=>$src){
					if(!empty($settings['_assetLabel'])){
						if( in_array($src, $settings['_assetLabel']) ){
							$assetslug = array_search($src, $settings['_assetLabel']);
							$alwaysload_headers .= "\r\n			wp_enqueue_script( '{{_alwaysloadSlug}}-".$src."', self::get_url( '".$settings['_assetURL'][$assetslug]."', __FILE__ ), array( 'jquery' ) , false, ".($settings['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );";
						}else{
							if(false !== strpos($src, '/')){
								$alwaysload_headers .= "\r\n			wp_enqueue_script( '{{_alwaysloadSlug}}-".sanitize_key( $src )."', '".$src."', false , false, ".($settings['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );";
							}else{
								$alwaysload_headers .= "\r\n			wp_enqueue_script( '".$src."', false, false , false, ".($settings['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );";
							}
						}
					}else{
						if(false !== strpos($src, '/')){
							$alwaysload_headers .= "\r\n			wp_enqueue_script( '{{_alwaysloadSlug}}-".sanitize_key( $src )."', '".$src."', false , false, ".($settings['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );";
						}else{
							$alwaysload_headers .= "\r\n			wp_enqueue_script( '".$src."', false, false , false, ".($settings['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );";
						}
					}
				}
			}

			// Template Placement
			// 2 header
			// 3 prepend
			// 4 append
			// 5 footer

			switch($settings['_alwaysLoadPlacement']){
				case '2':
					if(empty($alwaysload_headermethod)){
						$alwaysload_headermethod = true;
						$alwaysload_methods .= str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/alwaysload-headermethod.php'));
						$alwaysload_actions .= "\r\n		add_action('wp_head', array( \$this, 'header_template' ));\r\n";
					}
					$alwaysload_headtemplate .= "\r\n		echo \$this->render_element(get_option( \"_".$settings['_shortcode']."_options\" ), false, '".$settings['_shortcode']."');\r\n";
					
				break;
				case '3':

					if(empty($alwaysload_contentplacement)){
						$alwaysload_contentplacement = true;
						$alwaysload_actions .= "\r\n		foreach(\$wp_query->posts as &\$post){;\r\n	{{content_placements}}\r\n		}";
					}
					$alwaysload_contenttemplate .= "\r\n			\$post->post_content = \$this->render_element(get_option( \"_".$settings['_shortcode']."_options\" ), false, '".$settings['_shortcode']."') . \$post->post_content;\r\n";
					
				break;
				case '4':
					if(empty($alwaysload_contentplacement)){
						$alwaysload_contentplacement = true;
						$alwaysload_actions .= "\r\n		foreach(\$wp_query->posts as &\$post){;\r\n	{{content_placements}}\r\n		}";
					}
					$alwaysload_contenttemplate .= "\r\n			\$post->post_content .= \$this->render_element(get_option( \"_".$settings['_shortcode']."_options\" ), false, '".$settings['_shortcode']."');\r\n";
					
				break;
				case '5':
					if(empty($alwaysload_footermethod)){
						$alwaysload_footermethod = true;
						$alwaysload_methods .= str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/alwaysload-footermethod.php'));
						$alwaysload_actions .= "\r\n		add_action('wp_footer', array( \$this, 'footer_template' ));\r\n";
					}
					$alwaysload_foottemplate .= "\r\n		echo \$this->render_element(get_option( \"_".$settings['_shortcode']."_options\" ), false, '".$settings['_shortcode']."');\r\n";
					
				break;
			}

			$alwaysload_headers = str_replace('{{_alwaysloadSlug}}', $settings['_shortcode'], $alwaysload_headers);
			if( $settings['_elementType'] == '5'){
				// settings - no front end
				$adminstyles .= "\r\n		if( false !== strpos( \$screen->base, '".$settings['_shortcode']."' ) ){\r\n";
					$adminstyles .= $alwaysload_headers . "\r\n";
				$adminstyles .= "		}\r\n		";
				$alwaysload_headers = null;
			}
		}
	//}
	//register settings
	$settings_template = str_replace('{{register_settings}}', $settings_register, $settings_template);


	// apply the definitions
	$settings_template = str_replace('{{settings_items}}', $settings_items, $settings_template);
	// write the template
	foreach($plugin as $variable=>$setting){
		$settings_template = str_replace('{{'.$variable.'}}', $setting, $settings_template);
	}				
	
	$dirpathmethods = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/'.$exporttype.'-dirpath.php'));
	$settings_template = str_replace('{{get_url_path}}', $dirpathmethods, $settings_template);

	$wp_filesystem->put_contents( $plugin_path . '/includes/settings.php', $settings_template, FS_CHMOD_FILE);
	$alwaysload_headers .= "\r\n" . $alwaysload_detect;


}



// replace stuff
$corefile = str_replace('{{settings}}', $settings_init, $corefile);
$classfile = str_replace('{{alwaysload_detect}}', $alwaysload_headers, $classfile);
//$classfile = str_replace('{{alwaysload_methods}}', $alwaysload_methods, $classfile);
$alwaysload_methods = str_replace('{{alwaysload_header}}', $alwaysload_headtemplate, $alwaysload_methods);
$alwaysload_methods = str_replace('{{alwaysload_footer}}', $alwaysload_foottemplate, $alwaysload_methods);

$alwaysload_methods = str_replace('{{content_placements}}', $alwaysload_contenttemplate, $alwaysload_methods);
$alwaysload_actions = str_replace('{{content_placements}}', $alwaysload_contenttemplate, $alwaysload_actions);

$export_methods .= $alwaysload_methods;

$classfile = str_replace('{{alwaysload_actions}}', $alwaysload_actions, $classfile);


