<?php

$widget_detect = "";
$widget_include = "";
$general_methods = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/general-methods.php'));
if(!empty($export['widget'])){

	$widget_detect = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/widget-detector.php'));
	foreach($export['widget'] as $widget){
		//dump($widget);
		$widget_panelsize = "";///"\r\n		,array( 'width' => 350)\r\n";
		$widget_nav = "";
		$widget_type = "";
		if(!empty($widget['_groupvals'])){

			if( ( count($widget['_groupvals']) > 1 && empty($widget['_posttype']) ) || $widget['_shortcodeType'] == 2 && empty($widget['_posttype'])){
				$widget_panelsize = "\r\n		,array( 'width' => 495)\r\n";;
				$widget_nav = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/widget-nav.php'));
				$widget_type = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/widget-single.php'));
			}elseif(!empty($widget['_posttype'])){
				$widget_panelsize = null;
				$widget_type = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/widget-post.php'));
			}else{
				$widget_type = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/widget-single.php'));
			}
		}else{
			if(!empty($widget['_posttype'])){
				$widget_panelsize = null;
				$widget_type = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/widget-post.php'));
			}
		}
		$widget_wrap_before = "";
		$widget_wrap_after = "";
		$widget_title = "";
		$widget_has_title = "";
		$widget_has_content = "";
		$widget_content = "";
		$widget_render = "echo \$element->render_element(\$instance, '', '{{_widgetSlug}}');";
		$widget_headers = "echo \$element->render_element(\$settings, '', '{{_widgetSlug}}', true);\r\n";
		if($widget['_widgetWrap'] == 2){
			$widget_wrap_before = "echo \$before_widget;\r\n";
			$widget_wrap_after = "echo \$after_widget;\r\n";

		}
		if($widget['_widgetTitle'] == 2){
			if(empty($widget['_posttype'])){
				$widget_has_title = "echo \"<p><label>Title: <input value=\\\"\".(!empty(\$instance['__title__']) ? \$instance['__title__'] : '').\"\\\" name=\\\"\".self::get_field_name('__title__').\"\\\" type=\\\"text\\\" id=\\\"\".self::get_field_id('__title__').\"\\\" class=\\\"widefat\\\"></label></p>".'\r\n'."\";\r\n";
				$widget_title = "		if(!empty(\$instance['__title__'])){\r\n				echo \$before_title.\$instance['__title__'].\$after_title;\r\n				unset(\$instance['__title__']);\r\n		}\r\n";
			}else{
				$widget_title = "if( !empty( \$instance['id'] ) ){\r\n		echo \$before_title.get_the_title(\$instance['id']).\$after_title;\r\n	}\r\n";
			}
		}

		if($widget['_shortcodeType'] == 2){
			$widget_has_content = "echo \"<li class=\\\"\" . ( !empty(\$instance['__cur_tab__']) ? (\$instance['__cur_tab__'] == (\$key+1) ? \"current\" : \"\") : ((\$key+1) === 0 ? \"current\" : \"\" )) . \"\\\">".'\r\n";'."\r\n";
			$widget_has_content .= "			echo \"	<a data-tabkey=\\\"\".(\$key+1).\"\\\" data-tabset=\\\"\".self::get_field_id('__cur_tab__').\"\\\" title=\\\"\". __( 'Content', '{{slug}}' ) .\"\\\" href=\\\"#row\".self::get_field_id('__content__').\"_content\\\"><strong>\".__( 'Content', '{{slug}}' ).\"</strong></a>".'\r\n";'."\r\n";
			$widget_has_content .= "			echo \"</li>".'\r\n";'."\r\n";
			
			$widget_content  = "echo \"<div id=\\\"row\".self::get_field_id('__content__').\"_content\\\" class=\\\"{{slug}}-groupbox group\\\" \" . ( !empty(\$instance['__cur_tab__']) ? (\$instance['__cur_tab__'] == (\$key+1) ? \"\" : \"style=\\\"display:none;\\\"\") : ((\$key+1) === 0 ? \"\" : \"style=\\\"display:none;\\\"\" )) . \">".'\r\n";'."\r\n";
			$widget_content .= "			echo \"	<h3>\".__('Content' , '{{slug}}').\"</h3>".'\r\n";'."\r\n";
			$widget_content .= "			echo \"	<textarea name=\\\"\".self::get_field_name('__content__').\"\\\" id=\\\"\".self::get_field_id('__content__').\"\\\" cols=\\\"20\\\" rows=\\\"16\\\" class=\\\"widefat\\\">\".(!empty(\$instance['__content__']) ? htmlentities(\$instance['__content__']) : '').\"</textarea>".'\r\n";'."\r\n";
			$widget_content .= "			echo \"</div>".'\r\n";'."\r\n";

			if(!empty($widget['_posttype'])){

				$widget_render = "if( empty( \$instance['id'] ) ){ return; }\r\n		echo \$element->render_element(\$instance, get_post_field('post_content', \$instance['id']), '{{_widgetSlug}}');";
				$widget_headers = "if( empty( \$settings['id'] ) ){ return; }\r\n		\$element->render_element(\$settings, get_post_field('post_content', \$settings['id']), '{{_widgetSlug}}', true);\r\n";
			}else{
				$widget_render = "echo \$element->render_element(\$instance, \$instance['__content__'], '{{_widgetSlug}}');";
				$widget_headers = "\$content = null;\r\n							";
				$widget_headers .= "if(isset(\$settings['__content__'])){\r\n								";
				$widget_headers .= "\$content = \$settings['__content__'];\r\n							";
				$widget_headers .= "}\r\n							";				
				$widget_headers .= "\$element->render_element(\$settings, \$content, '{{_widgetSlug}}', true);\r\n";
			}			
		}
		// export headers -css
		if(!empty($widget['_cssLib'])){
			// enqueue scripts
			foreach($widget['_cssLib'] as $csskey=>$src){
				if(!empty($widget['_assetLabel'])){
					if( in_array($src, $widget['_assetLabel']) ){
						$assetslug = array_search($src, $widget['_assetLabel']);
						$widget_headers .= "							wp_enqueue_style( '{{_widgetSlug}}-".$src."', self::get_url( '".$widget['_assetURL'][$assetslug]."', dirname(__FILE__) ) );\r\n";
					}else{
						if(false !== strpos($src, '/')){
							$widget_headers .= "						wp_enqueue_style( '{{_widgetSlug}}-".sanitize_key(basename($src))."', '".$src."' );\r\n";
						}else{
							$widget_headers .= "						wp_enqueue_style( '". strtolower( $src ) ."' );\r\n";
						}
					}
				}else{
					if(false !== strpos($src, '/')){
						$widget_headers .= "						wp_enqueue_style( '{{_widgetSlug}}-".sanitize_key(basename($src))."', '".$src."' );\r\n";
					}else{
						$widget_headers .= "						wp_enqueue_style( '" . strtolower( $src ) . "' );\r\n";
					}
				}
			}
		}
		// export headers -js
		if(!empty($widget['_jsLib'])){
			// enqueue scripts
			foreach($widget['_jsLib'] as $jskey=>$src){
				if(!empty($widget['_assetLabel'])){
					if( in_array($src, $widget['_assetLabel']) ){
						$assetslug = array_search($src, $widget['_assetLabel']);
						$widget_headers .= "							wp_enqueue_script( '{{_widgetSlug}}-".$src."', self::get_url( '".$widget['_assetURL'][$assetslug]."', dirname(__FILE__) ), array( 'jquery' ) , false, ".($widget['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );\r\n";
					}else{
						if(false !== strpos($src, '/')){
							$widget_headers .= "							wp_enqueue_script( '{{_widgetSlug}}-".sanitize_key(basename($src))."', '".$src."', false , false, ".($widget['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );\r\n";
						}else{
							$widget_headers .= "							wp_enqueue_script( '". strtolower( $src ) ."', false, false , false, ".($widget['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );\r\n";
						}
					}
				}else{
					if(false !== strpos($src, '/')){
						$widget_headers .= "							wp_enqueue_script( '{{_widgetSlug}}-".sanitize_key(basename($src))."', '".$src."', false , false, ".($widget['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );\r\n";
					}else{
						$widget_headers .= "							wp_enqueue_script( '". strtolower( $src ) ."', false, false , false, ".($widget['_jsLibLoc'][$jskey] == 2 ? 'true' : 'false')." );\r\n";
					}
				}
			}
		}

		$widget_template = $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/widget.php');
		$widget_template = str_replace('{{widget_type}}', $widget_type, $widget_template);
		$widget_template = str_replace('{{widget_has_nav}}', $widget_nav, $widget_template);
		$widget_template = str_replace('{{widget_panelsize}}', $widget_panelsize, $widget_template);
		$widget_template = str_replace('{{widget_before}}', $widget_wrap_before, $widget_template);
		$widget_template = str_replace('{{widget_after}}', $widget_wrap_after, $widget_template);
		$widget_template = str_replace('{{widget_has_title}}', $widget_has_title, $widget_template);
		$widget_template = str_replace('{{widget_has_content}}', $widget_has_content, $widget_template);
		$widget_template = str_replace('{{widget_content}}', $widget_content, $widget_template);
		$widget_template = str_replace('{{widget_title}}', $widget_title, $widget_template);
		$widget_template = str_replace('{{widget_render}}', $widget_render, $widget_template);
		$widget_template = str_replace('{{widget_headers}}', $widget_headers, $widget_template);
		$widget_template = str_replace('{{_widgetClass}}', $widget['_shortcode'], $widget_template);
		$widget_template = str_replace('{{_widgetSlug}}', $widget['_shortcode'], $widget_template);
		$widget_template = str_replace('{{_widgetName}}', $widget['_name'], $widget_template);
		$widget_template = str_replace('{{_widgetDesc}}', $widget['_description'], $widget_template);
		

		// Paths & Directories
		$dirpathmethods = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/'.$exporttype.'-dirpath.php'));
		$widget_template = str_replace('{{get_url_path}}', $dirpathmethods, $widget_template);


		foreach($plugin as $variable=>$setting){
			$widget_template = str_replace('{{'.$variable.'}}', $setting, $widget_template);
		}
		//dump($widget_template);
		//dump($widget);
		$wp_filesystem->put_contents($plugin_path . '/includes/widget-'.$widget['_shortcode'].'.php', $widget_template, FS_CHMOD_FILE);

		// append widget to declaration
		$widget_include .= "require_once( {{core_include_path}}/includes/widget-".$widget['_shortcode'].".php' );\r\n";
	}
	
	//dump($export);
	//dump($widget_template);
}
// replacements
$corefile = str_replace('{{widgets}}', $widget_include, $corefile);
$classfile = str_replace('{{widget_detect}}', $widget_detect, $classfile);
