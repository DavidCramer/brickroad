<?php

// Build preview
$hasVars = false;

if(!empty($export['preview'])){
	
	if(!isset($settings_init)){
		$settings_init = null;
	}
	
	$settings_elements = array();
	$settings_elements = array_merge($settings_elements, $export['preview']);
	
	$preview_actions = null;
	$preview_methods = null;
	$preview_foottemplate = null;
	$preview_headtemplate = null;

		foreach($settings_elements as $settingsSlug=>$settings){
			// export headers -css
			if(!empty($settings['_cssLib'])){
				// enqueue scripts
				foreach($settings['_cssLib'] as $csskey=>$src){
					if(!empty($settings['_assetLabel'])){
						if( in_array($src, $settings['_assetLabel']) ){
							$assetslug = array_search($src, $settings['_assetLabel']);
							$adminstyles .= "\r\n			wp_enqueue_style( '{{_previewSlug}}-".$src."', self::get_url( '".$settings['_assetURL'][$assetslug]."', dirname(__FILE__) ) );";
						}else{
							if(false !== strpos($src, '/')){
								$adminstyles .= "\r\n			wp_enqueue_style( '{{_previewSlug}}-".sanitize_key( $src )."', '".$src."' );";
							}else{
								$adminstyles .= "\r\n			wp_enqueue_style( '".$src."' );";
							}
						}
					}else{
						if(false !== strpos($src, '/')){
							$adminstyles .= "\r\n			wp_enqueue_style( '{{_previewSlug}}-".sanitize_key( $src )."', '".$src."' );";
						}else{
							$adminstyles .= "\r\n			wp_enqueue_style( '".$src."' );";
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
							$adminstyles .= "\r\n			wp_enqueue_script( '{{_previewSlug}}-".$src."', self::get_url( '".$settings['_assetURL'][$assetslug]."', dirname(__FILE__) ), array( 'jquery' ) , false, ".($settings['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );";
						}else{
							if(false !== strpos($src, '/')){
								$adminstyles .= "\r\n			wp_enqueue_script( '{{_previewSlug}}-".sanitize_key( $src )."', '".$src."', false , false, ".($settings['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );";
							}else{
								$adminstyles .= "\r\n			wp_enqueue_script( '".$src."', false, false , false, ".($settings['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );";
							}
						}
					}else{
						if(false !== strpos($src, '/')){
							$adminstyles .= "\r\n			wp_enqueue_script( '{{_previewSlug}}-".sanitize_key( $src )."', '".$src."', false , false, ".($settings['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );";
						}else{
							$adminstyles .= "\r\n			wp_enqueue_script( '".$src."', false, false , false, ".($settings['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );";
						}
					}
				}
			}

			// Template Placement
			// 2 header
			// 3 prepend
			// 4 append
			// 5 footer
			$preview_has_content = null;
			$preview_content = null;
			if($element['_shortcodeType'] == '2'){

				$preview_has_content .= "echo \"<li class=\\\"\" . ( !empty(\$instance['__cur_tab__']) ? (\$instance['__cur_tab__'] == (\$key+1) ? \"current\" : \"\") : ((\$key+1) === 0 ? \"current\" : \"\" )) . \"\\\">".'\r\n";'."\r\n";
				$preview_has_content .= "			echo \"	<a data-tabkey=\\\"\".(\$key+1).\"\\\" data-tabset=\\\"".$settings['_shortcode']."__cur_tab__\\\" title=\\\"\". __( 'Content', '".$settings['_shortcode']."' ) .\"\\\" href=\\\"#row".$settings['_shortcode']."__content___content\\\"><strong>\".__( 'Content', '".$settings['_shortcode']."' ).\"</strong></a>".'\r\n";'."\r\n";
				$preview_has_content .= "			echo \"</li>".'\r\n";'."\r\n";
				
				$preview_content .= "echo \"<div id=\\\"row".$settings['_shortcode']."__content___content\\\" class=\\\"{{slug}}-groupbox group\\\" \" . ( !empty(\$instance['__cur_tab__']) ? (\$instance['__cur_tab__'] == (\$key+1) ? \"\" : \"style=\\\"display:none;\\\"\") : ((\$key+1) === 0 ? \"\" : \"style=\\\"display:none;\\\"\" )) . \">".'\r\n";'."\r\n";
				$preview_content .= "			echo \"	<h3>\".__('Content' , '".$settings['_shortcode']."').\"</h3>".'\r\n";'."\r\n";
				$preview_content .= "			echo \"	<textarea name=\\\"".$settings['_shortcode']."[__content__]\\\" id=\\\"".$settings['_shortcode']."__content__\\\" cols=\\\"20\\\" rows=\\\"16\\\" class=\\\"widefat\\\">\".(!empty(\$instance['__content__']) ? htmlentities(\$instance['__content__']) : '').\"</textarea>".'\r\n";'."\r\n";
				$preview_content .= "			echo \"</div>".'\r\n";'."\r\n";

			}


			$preview_methods .= str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/preview-footermethod.php'));
			$adminstyles .= "\r\n		add_action('wp_footer', array( \$this, 'footer_template' ));\r\n";
			
			$adminstyles .= "\r\n		\$args = get_transient( \"_".$settings['_shortcode']."_preview\" );\r\n";
			$adminstyles .= "\r\n		\$this->render_element(\$args, ( !empty(\$args['__content__']) ? \$args['__content__'] : ''), '".$settings['_shortcode']."', true);\r\n";
			//$adminstyles .= "\r\n		\$this->plugin_screen_hook_suffix['".$settings['_shortcode']."'] = 'element_preview';\r\n";
			//$adminstyles .= "\r\n		\$screen = (object)array('id'=>'element_preview');\r\n";

			

			$preview_foottemplate .= "\r\n		\$args = get_transient( \"_".$settings['_shortcode']."_preview\" );\r\n";
			$preview_foottemplate .= "\r\n		echo \$this->render_element(\$args, ( !empty(\$args['__content__']) ? \$args['__content__'] : ''), '".$settings['_shortcode']."');\r\n";

			$adminstyles = str_replace('{{_previewSlug}}', $settings['_shortcode'], $adminstyles);
			
			$atts_methods = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/preview-build-attribute.php'));
			$atts_nav = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/metabox-nav.php'));

			$atts_methods = str_replace('{{preview_has_content}}', $preview_has_content, $atts_methods);
			$atts_methods = str_replace('{{preview_content}}', $preview_content, $atts_methods);			

			$export_methods .= str_replace('{{has_meta_nav}}', $atts_nav, $atts_methods);


		}


	$corefile = str_replace('{{settings}}', $settings_init, $corefile);
	$classfile = str_replace('{{alwaysload_detect}}', $adminstyles, $classfile);

	$preview_methods = str_replace('{{preview_footer}}', $preview_foottemplate, $preview_methods);


	$export_methods .= $preview_methods;


}


