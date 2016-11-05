<?php

// Build Post Types
$usedfields = array();
$metabox_detect = "";
if(!empty($export['metabox'])){	
	$export_inits .= "add_action( 'init', array( \$this, 'activate_metaboxes' ) );\r\n";

	$metabox_methods = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/metabox.php'));
	$metabox_metaboxes = "";
	$metaboxes_block = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/metabox-methods.php'));
	$metabox_posttypes = array();
	$metabox_dash = '';
	foreach($export['metabox'] as $metabox){
		if(empty($metabox['_metabox_posttype'])){
			// default to page and post.
			$metabox['_metabox_posttype'] = array(
            	"post" => "post",
            	"page" => "page"
            );
		}
		$metabox_posttypes = array_merge($metabox_posttypes, $metabox['_metabox_posttype']);
		// add meta box
		//$metabox_methods = "";				
		$metabox_action = "";
		//if(!empty($metabox['_variable'])){
			if(in_array('dashboard', $metabox['_metabox_posttype'])){
				$metabox_dash = "		add_action('wp_dashboard_setup', array(\$this, 'add_metaboxes') );\r\n";
			}
			

			$metabox_action = "add_action('add_meta_boxes', array(\$this, 'add_metaboxes'), 5, 4);\r\n";
			$metabox_action .= "		add_action('save_post', array(\$this, 'save_post_metaboxes'), 1, 2);\r\n";
			// add script and style includes
			// Need to have the assets included
			//$metabox['_assetURL']
			if(!empty($metabox['_cssLib'])){
				foreach($metabox['_cssLib'] as $csslibkey=>$csslib){
					// check if url or key
					if(false !== strpos($csslib, '/')){
						// is a url.
						$metabox_metaboxes .= "\r\n		wp_enqueue_style( 'style-".$csslibkey."', '".$csslib."', array(), self::VERSION );";
					}else{

						// check if a slug of an asset or a built in jslib
						//slug
						$is_slug = false;
						if(!empty($metabox['_assetLabel'])){							
							foreach($metabox['_assetLabel'] as $assetkey=>$label){								
								if($csslib == $label){
									$is_slug = true;
									$metabox_metaboxes .= "\r\n		wp_enqueue_style( \$this->plugin_slug . '-".sanitize_key($label)."', self::get_url( '".ltrim($metabox['_assetURL'][$assetkey], '/')."', __FILE__ ), array(), self::VERSION );";
									break;
								}
							}
						}
					}
				}
			}

			if(!empty($metabox['_jsLib'])){
				foreach($metabox['_jsLib'] as $jslibkey=>$jslib){
					// check if url or key
					if(false !== strpos($jslib, '/')){
						// is a url.
						$metabox_metaboxes .= "\r\n		wp_enqueue_script( 'script-".$jslibkey."', '".$jslib."', array( 'jquery' ), self::VERSION );";
					}else{

						// check if a slug of an asset or a built in jslib
						//slug
						$is_slug = false;
						if(!empty($metabox['_assetLabel'])){							
							foreach($metabox['_assetLabel'] as $assetkey=>$label){								
								if($jslib == $label){
									$is_slug = true;
									$metabox_metaboxes .= "\r\n		wp_enqueue_script( \$this->plugin_slug . '-".sanitize_key($label)."', self::get_url( '".ltrim($metabox['_assetURL'][$assetkey], '/')."', __FILE__ ), array( 'jquery' ), self::VERSION );";
									break;
								}
							}
						}
						// if ref
						if(empty($is_slug)){
							$metabox_metaboxes .= "\r\n		wp_enqueue_script( '".sanitize_title($jslib)."' );";
						}
					}
				}
			}
			if(!empty($metabox['_type'])){
				foreach($metabox['_type'] as $type){
					$type = sanitize_key($type);
					if(!in_array($type, $usedfields)){

						if(!empty($fieldtype[$type]['scripts']) || !empty($fieldtype[$type]['styles'])){
							if(!empty($fieldtype[$type]['styles'])){
								foreach($fieldtype[$type]['styles'] as $style){
									$metabox_metaboxes .= "\r\n		wp_enqueue_style( \$this->plugin_slug . '-".$type."-styles-".str_replace('.', '-', $style)."', self::get_url( 'assets/css/".$style."', __FILE__ ), array(), self::VERSION );";
								}
							}
							if(!empty($fieldtype[$type]['scripts'])){
								foreach($fieldtype[$type]['scripts'] as $script){
									$metabox_metaboxes .= "\r\n		wp_enqueue_script( \$this->plugin_slug . '-".$type."-script-".str_replace('.', '-', $script)."', self::get_url( 'assets/js/".$script."', __FILE__ ), array( 'jquery' ), self::VERSION );";
								}
							}
						}
						$usedfields[] = $type;
					}
				}
			}
			// add scripts assets if template build type

			//build fieldtypes
			$buildtype = 'render_metaboxes';			
			if($metabox['_meta_build'] == 'template'){
				$buildtype = 'render_metaboxes_custom';
				if(!empty($metabox['_cssCode'])){
					//dump($metabox,0);
					$metabox_metaboxes .= "\r\n		wp_enqueue_style( \$this->plugin_slug . '-".$metabox['_shortcode']."-styles', self::get_url( 'assets/css/styles-" . $metabox['_shortcode'] . ".css', __FILE__ ), array(), self::VERSION );";
				}

				if(empty($meta_custom)){					
					$metabox_methods .= str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/metabox-build-template.php'));
					$meta_custom = true;
				}
			}else{
				if(empty($meta_attr)){
					$metabox_methods .= str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/metabox-build-attribute.php'));
					//if(!empty($metabox['_groupvals'])){
						//if(count($metabox['_groupvals']) > 1){
							$metabox_nav = str_replace('<?php', '', $wp_filesystem->get_contents(BRICKROAD_PATH.'/templates/metabox-nav.php'));
							$metabox_methods = str_replace('{{has_meta_nav}}', $metabox_nav, $metabox_methods);
						//}else{
						//	$metabox_methods = str_replace('{{has_meta_nav}}', '', $metabox_methods);
						//}
					//}else{
					//	$metabox_methods = str_replace('{{has_meta_nav}}', '', $metabox_methods);
					//}

					$meta_attr = true;
				}
			}
			if(!empty($metabox['_metabox_posttype'])){
				$groups = array();
				if(!empty($metabox['_groupvals'])){
					foreach($metabox['_groupvals'] as $group){
						$groups[] = "'".sanitize_file_name($group)."'";
						//$group_slug = sanitize_key($group);
						// $varkeys = array_keys($metabox['_tabgroup'], $group);
						//// SETUP GROUPS FOR ATTRIBUTES
						// add script and style includes

					}
				}
				$groups = "array(".implode(',',$groups).")";			

				foreach($metabox['_metabox_posttype'] as $meta_posttype){
					if( empty( $metabox['_meta_store'] ) ){
						$metabox['_meta_store'] = 'array';
					}
					$metabox_metaboxes .= "\r\n		add_meta_box('".$metabox['_shortcode']."', '".$metabox['_name']."', array(\$this, '".$buildtype."'), '".$meta_posttype."', '".$metabox['_meta_context']."', '".$metabox['_meta_priority']."', array( 'slug' => '". $metabox['_shortcode'] ."', 'groups' => ".$groups.", 'store' => '".$metabox['_meta_store']."' ) );";
				}
			}
		//}

	}
	//meta_build
	$metaboxes_block = str_replace('{{add_meta_boxes}}', $metabox_metaboxes, $metaboxes_block);

	//$temp_block = str_replace('{{add_metabox}}', "", $temp_block);
	foreach($metabox as $setting=>$value){
		if(is_array($value)){continue;}

		//$temp_block = str_replace('{{'.$setting.'}}', $value, $temp_block);
	}
	//$metabox_block .= $temp_block;

	//dump($usedfields);
	$metabox_methods = str_replace('{{setup_metabox}}', $metabox_action.$metabox_dash, $metabox_methods);
	$metabox_methods = str_replace('{{meta_boxes}}', $metaboxes_block, $metabox_methods);

	// posttype loopout
	foreach($metabox_posttypes as &$type){
		$type = "'".$type."'";
	}

	$metabox_methods = str_replace('{{switch_types}}', "if(!empty(\$post)){\r\n			if(!in_array(\$post->post_type, array(".implode(',',$metabox_posttypes)."))){return;}\r\n		}else{\r\n				\$screen = get_current_screen();\r\n				if(!in_array(\$screen->base, array(".implode(',',$metabox_posttypes)."))){return;}\r\n			}\r\n", $metabox_methods);

	// replace shortcode

	$export_methods .= $metabox_methods;
	//dump($metabox_posttypes);
}
