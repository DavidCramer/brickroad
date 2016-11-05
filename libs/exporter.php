<?php


function brickroad_exportPluginPro( $data, $exporttype = 'plugin' ) {
	global $wp_version, $wp_filesystem;

	if ( empty( $data[ 'export' ] ) ) {
		return;
	}

	// okay, let's see about getting
	$url = wp_nonce_url( 'admin.php?page=brickroad-admin', 'brickroad-admin-element' );
	if ( false === ( $creds = request_filesystem_credentials( $url ) ) ) {
		// if we get here, then we don't have credentials yet,
		// but have just produced a form for the user to fill in,
		// so stop processing for now
		return true; // stop the normal page form from displaying
	}
	// now we have some credentials, try to get the wp_filesystem running
	WP_Filesystem( $creds );

	$data = stripslashes_deep( $data );
	$plugin = array(
		'slug' => sanitize_title( $data[ '_pluginName' ] ),
		'key' => str_replace( '-', '_', sanitize_title( $data[ '_pluginName' ] ) ),
		'_pluginClass' => str_replace( ' ', '_', ucwords( str_replace( '-', ' ', sanitize_title( $data[ '_pluginName' ] ) ) ) ),
		'_pluginName' => $data[ '_pluginName' ],
		'_pluginURI' => $data[ '_pluginURI' ],
		'_pluginDescription' => $data[ '_pluginDescription' ],
		'_pluginAuthor' => $data[ '_pluginAuthor' ],
		'_pluginAuthorEmail' => $data[ '_pluginAuthorEmail' ],
		'_pluginVersion' => $data[ '_pluginVersion' ],
		'_pluginAuthorURI' => $data[ '_pluginAuthorURI' ],
		'_year' => date( 'Y' ),
		'_date' => date( 'r' ),

	);

	// build plugincore.
	//dump($exporttype);
	switch ( $exporttype ) {
		case 'preview':
			$plugin_path = BRICKROAD_PATH . "elements/" . $plugin[ 'slug' ];
			$corefile = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/plugincore.php' );
			$classfile = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/class-preview-core.php' );
			break;
		case 'plugin':
			$plugin_path = plugin_dir_path( BRICKROAD_PATH ) . $plugin[ 'slug' ];
			$corefile = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/plugincore.php' );
			$classfile = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/class-plugin-name.php' );
			break;
		case 'theme':
			$plugin_path = trailingslashit( get_template_directory() ) . 'framework';
			$corefile = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/framework.php' );
			$classfile = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/class-plugin-name.php' );
			break;
	}	
	$pofile = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/po-file.po' );

	if ( file_exists( $plugin_path ) ) {
		$wp_filesystem->delete( $plugin_path, true );
	}
	// create new plugin folder
	$wp_filesystem->mkdir( $plugin_path );

	$types = array(
		1 => 'shortcode',
		2 => 'widget',
		3 => 'hybrid',
		4 => 'alwaysload',
		5 => 'settings',
		6 => 'post_type',
		7 => 'metabox',
		8 => 'template',
		9 => 'class',
		10 => 'element',
		20 => 'fieldtype',
		'preview' => 'preview'
	);

	// Gather Export Types
	$export = array();
	$adminstyles = null;
	$categories = array();
	//Write folders
	if ( !file_exists( $plugin_path . '/languages' ) ) {        
		$wp_filesystem->mkdir( $plugin_path . '/languages' );
	}
	if ( !file_exists( $plugin_path . '/assets' ) ) {
		$wp_filesystem->mkdir( $plugin_path . '/assets' );
	}
	if ( !file_exists( $plugin_path . '/assets/images' ) ) {
		$wp_filesystem->mkdir( $plugin_path . '/assets/images' );
	}
	if ( !file_exists( $plugin_path . '/assets/css' ) ) {
		$wp_filesystem->mkdir( $plugin_path . '/assets/css' );
	}
	if ( !file_exists( $plugin_path . '/assets/js' ) ) {
		$wp_filesystem->mkdir( $plugin_path . '/assets/js' );
	}

	$functions_lib = "";

	// SETUP SECTION VARS
	$export_inits = null;
	$export_methods = null;


	// Export processors
	$field_processor = array();
	$field_processor_method = str_replace( '<?php', '', $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/field-processor.php' ) );
	$field_processors = "default:\r\n				return \$value;\r\n				break;";
	foreach ( $data[ 'export' ] as $eid ) {
		$element = brickroad_get_element( $eid );
		if ( !empty( $element[ '_variable' ] ) ) {
			//build the field processor
			if ( !empty( $element[ '_type' ] ) ) {
				foreach ( $element[ '_type' ] as $typeKey => $field_type ) {
					if ( !isset( $field_processor[ $field_type ] ) ) {
						$field_processor[ $field_type ] = true;
						if ( file_exists( BRICKROAD_PATH . '/templates/fields/process-' . sanitize_key( $field_type ) . '.php' ) ) {
							$field_processors .= str_replace( '<?php', '', $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/fields/process-' . sanitize_key( $field_type ) . '.php' ) );
						}
					}
				}
			}
		}
	}

	$field_processor_method = str_replace( '{{field_processors}}', $field_processors, $field_processor_method );
	$export_methods .= $field_processor_method;

	// Element Export Modules
	$element_exporters = glob( BRICKROAD_PATH . '/libs/element-modules/*.php' );


	foreach ( $data[ 'export' ] as $eid ) {


		$element = get_option( $eid );
		if ( empty( $element ) ) {
			continue;
		}
		if($exporttype == 'preview'){
			$element[ '_elementType' ] = 'preview';
		}

		$type = $types[ $element[ '_elementType' ] ];
		if ( !empty( $element[ '_posttype' ] ) && file_exists( BRICKROAD_PATH . '/libs/element-modules/post_type.php' ) ) {
			if ( $type == 'shortcode' || $type == 'widget' || $type == 'hybrid' ) {
				$export[ 'post_type' ][ $element[ '_shortcode' ] ] = $element;
			}
		}

		if ( !file_exists( $plugin_path . '/assets' ) ) {
			$wp_filesystem->mkdir( $plugin_path . '/assets' );
		}
		if ( !file_exists( $plugin_path . '/includes' ) ) {
			$wp_filesystem->mkdir( $plugin_path . '/includes' );
		}


		//if(!empty($element['_assetURL'])){
		//	_jsLib
		//};
		if ( !empty( $element[ '_assetURL' ] ) ) {

			$assets_template = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/assets.php' );

			$assets = "\$assets = array(\r\n";
			foreach ( $element[ '_assetURL' ] as $assetslug => &$asset ) {
				if(empty($asset)){
					continue;
				}
				if ( floatVal( $asset ) > 0 ) {
					// media
					//dump($asset);
					$assettype = get_post_mime_type( $asset );
					$file = get_attached_file( $asset );
					switch ( $assettype ) {
						case 'application/javascript':
							$assetfolder = '/assets/js/';
							break;
						case 'text/css':
							$assetfolder = '/assets/css/';
							break;
						case 'image/png':
						case 'image/gif':
						case 'image/jpg':
						case 'image/jpeg':
							$assetfolder = '/assets/images/';
							break;
						default:
							if ( !file_exists( $plugin_path . '/assets/files' ) ) {
								$wp_filesystem->mkdir( $plugin_path . '/assets/files' );
							}
							$assetfolder = '/assets/files/';
							break;
					}

					$file = get_attached_file( $asset );
					$filename = basename( $file );
					//$wp_filesystem->put_contents( $path.'.'.$extension, $code, FS_CHMOD_FILE);
				} else {
					//url

					$dirs = wp_upload_dir();
					$file = str_replace( $dirs[ 'baseurl' ], $dirs[ 'basedir' ], $asset );
					$filedet = pathinfo( $file );
					//dump($asset);
					$filename = $filedet[ 'basename' ];
					switch ( strtolower( $filedet[ 'extension' ] ) ) {
						case 'css':
							$assetfolder = '/assets/css/';
							break;
						case 'js':
							$assetfolder = '/assets/js/';
							break;
						case 'png':
						case 'jpg':
						case 'gif':
						case 'jpeg':
							$assetfolder = '/assets/images/';
							break;
						default:
							if ( !file_exists( $plugin_path . '/assets/files' ) ) {
								$wp_filesystem->mkdir( $plugin_path . '/assets/files' );
							}
							$assetfolder = '/assets/files/';
							break;
					}
				}
				$data = $wp_filesystem->get_contents( $file );
				$wp_filesystem->put_contents( $plugin_path . $assetfolder . $filename, $data, FS_CHMOD_FILE );
				$asset = $assetfolder . $filename;
				$assets .= "	'" . $element[ '_assetLabel' ][ $assetslug ] . "'						=>		self::get_url( '" . $asset . "' , dirname( __FILE__ ) ),\r\n";
			}

			$assets .= ");\r\n";
			$assets_template = str_replace( '{{assets}}', $assets, $assets_template );
			foreach ( $plugin as $variable => $setting ) {
				$assets_template = str_replace( '{{' . $variable . '}}', $setting, $assets_template );
			}
			$wp_filesystem->put_contents( $plugin_path . '/assets/assets-' . $element[ '_shortcode' ] . '.php', $assets_template, FS_CHMOD_FILE );
		}

		// Export field configs
		$field_processor = "";
		if ( !empty( $element[ '_variable' ] ) ) {
			// build field group config
			$element_field_groups_template = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/field-groups.php' );
			//{{element_field_groups}}
			$element_field_groups = "\$configfiles = array(\r\n";
			foreach ( $element[ '_groupvals' ] as $group ) {
				$group_slug = sanitize_key( $group );
				$varkeys = array_keys( $element[ '_tabgroup' ], $group );
				$fieldtype = brickroad_export_group_fields( $plugin, $plugin_path . '/configs/', $group, $element );
				//$element_field_groups .= "	array(\r\n		'label' => '".$group."',\r\n		'file' => self::get_path(__FILE__) . 'configs/".$element['_shortcode']."-".sanitize_file_name(strtolower($group)).".php', \r\n	),\r\n";
				$element_field_groups .= "	self::get_path( __FILE__ ) . '" . $element[ '_shortcode' ] . "-" . sanitize_file_name( strtolower( $group ) ) . ".php', \r\n";
			}
			$element_field_groups .= ");\r\n";

			$element_field_groups_template = str_replace( '{{element_field_groups}}', $element_field_groups, $element_field_groups_template );
			$element_field_groups_template = str_replace( '{{shortcode}}', $element[ '_shortcode' ], $element_field_groups_template );
			foreach ( $plugin as $variable => $setting ) {
				$element_field_groups_template = str_replace( '{{' . $variable . '}}', $setting, $element_field_groups_template );
			}

			//dump($element_field_groups);
			$wp_filesystem->put_contents( $plugin_path . "/configs/fieldgroups-" . $element[ '_shortcode' ] . ".php", $element_field_groups_template, FS_CHMOD_FILE );
		}

		$classfile = str_replace( '{{field_processor}}', $field_processor, $classfile );
		// setup categories
		//$element['_category'] = 'All Shortcodes';
		//$categories['all'] = $element['_category'];
		$categories[ sanitize_key( $element[ '_category' ] ) ] = $element[ '_category' ];

		// write templates
		if ( !empty( $element[ '_cssCode' ] ) ) {
			if ( !file_exists( $plugin_path . '/assets/css' ) ) {
				$wp_filesystem->mkdir( $plugin_path . '/assets/css' );
			}
			brickroad_write_template( $plugin_path . '/assets/css/styles-' . $element[ '_shortcode' ] . '_css', $element[ '_cssCode' ], $element[ '_shortcode' ], $element );
		}
		if ( !empty( $element[ '_phpCode' ] ) ) {
			$slug = $element[ '_shortcode' ];
			$prefix = $element[ '_elementType' ] == '9' ? 'class' : 'functions';
			brickroad_write_template( $plugin_path . "/includes/{$prefix}-{$slug}_php", "<?php\r\n" . $element[ '_phpCode' ] . "\r\n?>", $slug, $element );
			$functions_lib .= "require_once( {{core_include_path}}/includes/{$prefix}-{$slug}.php' );\r\n";
		}
		if ( !empty( $element[ '_mainCode' ] ) ) {
			$prefix = $element[ '_elementType' ] == '20' ? 'field' : 'element';
			$extension = $element[ '_elementType' ] == '20' ? 'php' : 'html';
			brickroad_write_template( $plugin_path . '/includes/'.$prefix.'-' . $element[ '_shortcode' ] . '_' . $extension, $element[ '_mainCode' ], $element[ '_shortcode' ], $element );
		}
		if ( !empty( $element[ '_javascriptCode' ] ) ) {
			if ( !file_exists( $plugin_path . '/assets/js' ) ) {
				$wp_filesystem->mkdir( $plugin_path . '/assets/js' );
			}
			brickroad_write_template( $plugin_path . '/assets/js/scripts-' . $element[ '_shortcode' ] . '_js', $element[ '_javascriptCode' ], $element[ '_shortcode' ], $element );
		}
		// setup export
		if ( $type === 'hybrid' ) {
			$export[ 'widget' ][ $element[ '_shortcode' ] ] = $element;
			$export[ 'shortcode' ][ $element[ '_shortcode' ] ] = $element;
		} else {
			$export[ $type ][ $element[ '_shortcode' ] ] = $element;
		}
	}
	// replace stuff
	$corefile = str_replace( '{{functions}}', $functions_lib, $corefile );


	// Include General Methods - defined by mods
	$general_methods = "";


	// CYCLE EXPORTERS
	foreach ( $element_exporters as $element_exporter ) {
		include $element_exporter;
	}
	// code and alwaysload // settings pages
	//include	BRICKROAD_PATH.'/libs/element-modules/alwaysload-settings.php';
	// Build Widgets
	//include	BRICKROAD_PATH.'/libs/element-modules/widget.php';
	// Build Shortcodes
	//include	BRICKROAD_PATH.'/libs/element-modules/shortcode.php';
	// Build Post Types
	//include	BRICKROAD_PATH.'/libs/element-modules/post_type.php';
	// Build Templates
	/* TODO */

	// Set general
	$export_methods .= $general_methods;
	// Replace methods
	$classfile = str_replace( '{{methods}}', $export_methods, $classfile );
	// Replace Inits
	$classfile = str_replace( '{{inits}}', $export_inits, $classfile );

	/// CLEAN UP AND CONSTANTS
	// admin styles
	$classfile = str_replace( '{{admin_styles}}', $adminstyles, $classfile );
	$classfile = str_replace( '{{activate_post_types}}', "", $classfile );
	$classfile = str_replace( '{{post_type_render_id}}', "", $classfile );
	$classfile = str_replace( '{{posttype_detector}}', "", $classfile );
	$classfile = str_replace( '{{alwaysload_detect}}', "", $classfile );
	$classfile = str_replace( '{{alwaysload_actions}}', "", $classfile );
	

	// settings cleanup
	$corefile = str_replace( '{{settings}}', "", $corefile );
	

	// Icon.png
	$icon = $wp_filesystem->get_contents( BRICKROAD_PATH . '/images/icon.png' );
	// White Icon.png
	$white = $wp_filesystem->get_contents( BRICKROAD_PATH . '/images/white.png' );
	// Panel.css
	$panelcss = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/css/panel.css' );
	// Panel.js
	$paneljs = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/scripts/panel.js' );

	// Paths & Directories
	$dirpathmethods = str_replace( '<?php', '', $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/' . $exporttype . '-dirpath.php' ) );
	$classfile = str_replace( '{{get_url_path}}', $dirpathmethods, $classfile );
	switch ( $exporttype ) {
		case 'preview':
			$corefile = str_replace( '{{core_include_path}}', "plugin_dir_path( __FILE__ ) . '", $corefile );
			$corename = 'preview.php';
			break;
		case 'plugin':
			$corefile = str_replace( '{{core_include_path}}', "plugin_dir_path( __FILE__ ) . '", $corefile );
			$corename = 'plugincore.php';
			break;
		case 'theme':
			$corefile = str_replace( '{{core_include_path}}', "get_template_directory() . '/framework", $corefile );
			$corename = 'framework.php';
			break;
	}


	// create labels and values
	foreach ( $plugin as $variable => $setting ) {

		$pofile = str_replace( '{{' . $variable . '}}', $setting, $pofile );
		$corefile = str_replace( '{{' . $variable . '}}', $setting, $corefile );
		$classfile = str_replace( '{{' . $variable . '}}', $setting, $classfile );
		$paneljs = str_replace( '{{' . $variable . '}}', $setting, $paneljs );
		$panelcss = str_replace( '{{' . $variable . '}}', $setting, $panelcss );
	}

	$wp_filesystem->put_contents( $plugin_path . '/' . $corename, $corefile, FS_CHMOD_FILE );
	$wp_filesystem->put_contents( $plugin_path . '/class-' . $plugin[ 'slug' ] . '.php', $classfile, FS_CHMOD_FILE );

	$wp_filesystem->put_contents( $plugin_path . '/languages/' . strtolower($plugin[ 'slug' ]) . '.po', $pofile, FS_CHMOD_FILE );

	$wp_filesystem->put_contents( $plugin_path . '/assets/css/panel.css', $panelcss, FS_CHMOD_FILE );
	$wp_filesystem->put_contents( $plugin_path . '/assets/js/panel.js', $paneljs, FS_CHMOD_FILE );
	$wp_filesystem->put_contents( $plugin_path . '/assets/images/icon.png', $icon, FS_CHMOD_FILE );
	$wp_filesystem->put_contents( $plugin_path . '/assets/images/white.png', $white, FS_CHMOD_FILE );


	//return;

	//brickroad_zip($plugin_path, BRICKROAD_PATH.'exports/'.sanitize_file_name(strtolower($data['_pluginName'])).'.zip');
	
}

function brickroad_export_group_fields( $plugin, $path, $group, $element ) {
	global $wp_filesystem;

	//fieldtype support files
	$fieldtype = array(
		'textfield' => array(
			'file' => 'field-textfield.php',
		),
		'smalltextfield' => array(
			'file' => 'field-smalltextfield.php',
		),
		'textbox' => array(
			'file' => 'field-textbox.php',
		),
		'contenteditor' => array(
			'file' => 'field-contenteditor.php',
		),
		'radio-inline' => array(
			'file' => 'field-radio-inline.php',
		),
		'checkbox' => array(
			'file' => 'field-checkbox.php',
		),
		'radio' => array(
			'file' => 'field-radio.php',
		),
		'checkbox-inline' => array(
			'file' => 'field-checkbox-inline.php',
		),
		'dropdown' => array(
			'file' => 'field-dropdown.php',
		),
		'image' => array(
			'file' => 'field-image.php',
			'scripts' => array(
				'image-modal.js'
			),
		),
		'posttypeselector' => array(
			'file' => 'field-posttypeselector.php',
		),
		'file' => array(
			'file' => 'field-file.php',
			'scripts' => array(
				'file-modal.js'
			),
		),
		'colorpicker' => array(
			'file' => 'field-colorpicker.php',
			'images' => array(
				'minicolor-trigger.png',
				'minicolor-colors.png'
			),
			'styles' => array(
				'minicolors.css'
			),
			'scripts' => array(
				'minicolors.js'
			),
		),
		'codeeditor' => array(
			'file' => 'field-codeeditor.php',
			'styles' => array(
				'editor.css'
			),
			'scripts' => array(
				'codemirror-compressed.js'
			),
		),
		'onoff' => array(
			'file' => 'field-onoff.php',
			'inline' => true,
			'styles' => array(
				'toggles.css',
			),
			'scripts' => array(
				'toggles.min.js'
			),
		),
		'slider' => array(
			'file' => 'field-slider.php',
			'inline' => true,
			'styles' => array(
				'simple-slider.css',
			),
			'scripts' => array(
				'simple-slider.min.js'
			),
		),
		'date' => array(
			'file' => 'field-date.php',
			'inline' => true,
			'styles' => array(
				'datepicker.css',
			),
			'scripts' => array(
				'bootstrap-datepicker.js'
			),
		),
		'fieldelement'  =>  array(
		)
	);
	$assets = array();
	$usedfields = array();

	if ( !file_exists( $path ) ) {
		$wp_filesystem->mkdir( $path );
	}

	$fieldconfig = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/field-config.php' );
	$group_fields = "";
	$varkeys = array_keys( $element[ '_tabgroup' ], $group );
	$group_slug = sanitize_key( $group );
	$group_fields .= "\$group = array(\r\n";
		if( empty( $varkeys )){
			wp_die('Problem Exporting Vars. Please edit the element and save. Then try again.');
		}
		
	$group_fields .= "	'label' => __('" . str_replace("'", "\'", $element[ '_tabgroup' ][ $varkeys[ 0 ] ] ). "','{{slug}}'),\r\n";
	$group_fields .= "	'id' => '" . $varkeys[ 0 ] . "',\r\n";
	$group_fields .= "	'master' => '" . $element[ '_variable' ][ $varkeys[ 0 ] ] . "',\r\n";
	$group_fields .= "	'fields' => array(\r\n";

	$groupstyles = array();
	$groupscripts = array();

	$groupconf = array_merge( $plugin, array(
		'group' => $group_slug,
		'groupName' => $group,
		'groupid' => $varkeys[ 0 ]
	) );


	foreach ( $varkeys as $varkey ) {

		// Check for a custom type
		if( 'fieldelement' == sanitize_key( $element[ '_type' ][ $varkey ] ) ){
			
			$fieldelement = get_option( $element[ '_variableDefault' ][ $varkey ] );
			
			// assets			
			$plugin_path = dirname( $path );
			$assets = array();
			if(!empty($fieldelement['_assetLabel'])){
				$assets = array_flip($fieldelement['_assetLabel']);

				foreach ( $fieldelement[ '_assetURL' ] as $assetslug => &$asset ) {
					// media
					//dump($asset);
					$assettype = get_post_mime_type( $asset );
					$file = get_attached_file( $asset );
					switch ( $assettype ) {
						case 'application/javascript':
							$assetfolder = '/assets/js/';
							break;
						case 'text/css':
							$assetfolder = '/assets/css/';
							break;
						case 'image/png':
						case 'image/gif':
						case 'image/jpg':
						case 'image/jpeg':
							$assetfolder = '/assets/images/';
							break;
						default:
							if ( !file_exists( $plugin_path . '/assets/files' ) ) {
								$wp_filesystem->mkdir( $plugin_path . '/assets/files' );
							}
							$assetfolder = '/assets/files/';
							break;
					}

					$file = get_attached_file( $asset );
					$asset = basename( $file );

					$data = $wp_filesystem->get_contents( $file );
					$wp_filesystem->put_contents( $plugin_path . $assetfolder . $asset, $data, FS_CHMOD_FILE );


				}
			}

			// FIELD STYLES
			if( !empty( $fieldelement['_cssCode'] ) ){
				$groupstyles[] = 'styles-' . $fieldelement['_shortcode'].'.css';
			}
			// FIELD SCRIPTS
			if( !empty( $fieldelement['_javascriptCode'] ) ){
				$groupscripts[] = 'scripts-' . $fieldelement['_shortcode'].'.js';
			}
			$element[ '_type' ][ $varkey ] = $fieldelement['_shortcode'];

			// js libs
			if( !empty( $fieldelement['_jsLib'] ) ){
				$remotes = array();
				foreach($fieldelement['_jsLib'] as $libkey=>$libsrc){
					if( !empty($assets[$libsrc])){
						$libsrc = $fieldelement[ '_assetURL' ][$assets[$libsrc]];
					}
					if( false === strpos($libsrc, '.')){
						$libsrc = sanitize_title($libsrc);
					}
					$position = $fieldelement['_jsLibLoc'][$libkey] == '2' ? 'footer' : 'head';
					//dump($position);
					$remotes[$libsrc] = $position;
				}
				$groupscripts['remote'] = $remotes;
			}
			//dump($groupscripts);
			// style libs
			if( !empty( $fieldelement['_cssLib'] ) ){
				
				foreach($fieldelement['_cssLib'] as $libsrc){
					if( !empty($assets[$libsrc])){
						$libsrc = $fieldelement[ '_assetURL' ][$assets[$libsrc]];
					}
					if( false === strpos($libsrc, '.')){
						$libsrc = $libsrc;
					}
					$groupstyles[] = $libsrc;
				}
			}
			//dump($groupstyles);

			// write templates
			if ( !empty( $fieldelement[ '_cssCode' ] ) ) {
				if ( !file_exists( $plugin_path . '/assets/css' ) ) {
					$wp_filesystem->mkdir( $plugin_path . '/assets/css' );
				}
				brickroad_write_template( $plugin_path . '/assets/css/styles-' . $fieldelement[ '_shortcode' ] . '_css', $fieldelement[ '_cssCode' ], $fieldelement[ '_shortcode' ], $fieldelement );
			}
			if ( !empty( $fieldelement[ '_mainCode' ] ) ) {
				brickroad_write_template( $plugin_path . '/includes/field-' . $fieldelement[ '_shortcode' ] . '_php', $fieldelement[ '_mainCode' ], $fieldelement[ '_shortcode' ], $fieldelement );
			}
			if ( !empty( $fieldelement[ '_javascriptCode' ] ) ) {
				if ( !file_exists( $plugin_path . '/assets/js' ) ) {
					$wp_filesystem->mkdir( $plugin_path . '/assets/js' );
				}
				brickroad_write_template( $plugin_path . '/assets/js/scripts-' . $fieldelement[ '_shortcode' ] . '_js', $fieldelement[ '_javascriptCode' ], $fieldelement[ '_shortcode' ], $fieldelement );
			}
		}

		$group_fields .= "		'" . $element[ '_variable' ][ $varkey ] . "'	=>	array(\r\n";
		$group_fields .= "			'label'		=> 	__('" . str_replace("'", "\'", $element[ '_label' ][ $varkey ] ) . "','{{slug}}'),\r\n";
		if(!empty($element[ '_variableInfo' ][ $varkey ])){
			$group_fields .= "			'caption'	=>	__('" . str_replace("'", "\'", $element[ '_variableInfo' ][ $varkey ] ) . "','{{slug}}'),\r\n";
		}else{
			$group_fields .= "          'caption'   =>  '',\r\n";
		}
		$group_fields .= "			'type'		=>	'" . sanitize_key( $element[ '_type' ][ $varkey ] ) . "',\r\n";
		$group_fields .= "			'default'	=> 	'" . $element[ '_variableDefault' ][ $varkey ] . "',\r\n";
		// is inline
		if ( !empty( $fieldtype[ sanitize_key( $element[ '_type' ][ $varkey ] ) ][ 'inline' ] ) ) {
			$group_fields .= "			'inline'	=> 	true,\r\n";
		}

		$group_fields .= "		),\r\n";
		
		// capture support files
		if ( !empty( $fieldtype[ sanitize_key( $element[ '_type' ][ $varkey ] ) ][ 'styles' ] ) ) {
			foreach ( $fieldtype[ sanitize_key( $element[ '_type' ][ $varkey ] ) ][ 'styles' ] as $style ) {
				if ( !in_array( $style, $assets ) ) {
					$assets[ ] = $style;
				}
				if ( !in_array( $style, $groupstyles ) ) {
					$groupstyles[ ] = $style;
				}
			}
		}
		if ( !empty( $fieldtype[ sanitize_key( $element[ '_type' ][ $varkey ] ) ][ 'scripts' ] ) ) {
			foreach ( $fieldtype[ sanitize_key( $element[ '_type' ][ $varkey ] ) ][ 'scripts' ] as $script ) {
				if ( !in_array( $script, $assets ) ) {
					$assets[ ] = $script;
				}
				if ( !in_array( $script, $groupscripts ) ) {
					$groupscripts[ ] = $script;
				}
			}
		}
		if ( !empty( $fieldtype[ sanitize_key( $element[ '_type' ][ $varkey ] ) ][ 'images' ] ) ) {
			foreach ( $fieldtype[ sanitize_key( $element[ '_type' ][ $varkey ] ) ][ 'images' ] as $image ) {
				if ( !in_array( $image, $assets ) ) {
					$assets[ ] = $image;
				}
			}
		}

		$type = sanitize_key( $element[ '_type' ][ $varkey ] );
		if ( !in_array( $type, $usedfields ) ) {
			$usedfields[ ] = $type;
		}

	}

	$group_fields .= "	),\r\n";

	if ( !empty( $groupstyles ) ) {
		$group_fields .= "	'styles'	=> array(\r\n";
		foreach ( $groupstyles as $style ) {
			$group_fields .= "		'" . $style . "',\r\n";
		}
		$group_fields .= "	),\r\n";
	}
	if ( !empty( $groupscripts ) ) {
		$group_fields .= "	'scripts'	=> array(\r\n";
		foreach ( $groupscripts as $script ) {
			if( !is_array($script)){
				$group_fields .= "		'" . $script . "',\r\n";
			}else{
				$group_fields .= "		'remote' => array(\r\n";
					foreach( $script as $remotesrc=>$scriptpos){
						$group_fields .= "			'".$remotesrc."'	=>	'".$scriptpos."',\r\n";        
					}
				$group_fields .= "		),\r\n";
			}
		}
		$group_fields .= "	),\r\n";
	}
	$isMulti = 'false';
	if ( !empty( $element[ '_isMultiple' ][ $varkeys[ 0 ] ] ) ) {
		$isMulti = 'true';
	}
	$group_fields .= "	'multiple'	=> " . $isMulti . ",\r\n";

	$group_fields .= ");\r\n";


	$fieldconfig = str_replace( '{{element_fields}}', $group_fields, $fieldconfig );
	foreach ( $groupconf as $field => &$value ) {
		$fieldconfig = str_replace( '{{' . $field . '}}', $value, $fieldconfig );
	}
	if ( !empty( $element[ '_assetLabel' ] ) ) {
		foreach ( $element[ '_assetLabel' ] as $asset => $assetlabel ) {
			$fieldconfig = str_replace( '\'{{' . $assetlabel . '}}\'', "self::get_url( '" . $element[ '_assetURL' ][ $asset ] . "', dirname( __FILE__ ) )", $fieldconfig );
		}
	}

	$wp_filesystem->put_contents( $path . $element[ '_shortcode' ] . '-' . sanitize_file_name( strtolower( $group ) ) . '.php', $fieldconfig, FS_CHMOD_FILE );
	// write fields
	if ( !empty( $usedfields ) ) {
		foreach ( $usedfields as $field ) {
			// copy the field assets
			if ( !empty( $fieldtype[ $field ][ 'styles' ] ) ) {
				foreach ( $fieldtype[ $field ][ 'styles' ] as $fieldstyle ) {
					$cssfile = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/css/' . $fieldstyle );
					foreach ( $groupconf as $varfield => &$value ) {
						$cssfile = str_replace( '{{' . $varfield . '}}', $value, $cssfile );
					}
					$wp_filesystem->put_contents( dirname( $path ) . '/assets/css/' . $fieldstyle, $cssfile, FS_CHMOD_FILE );
				}
			}
			if ( !empty( $fieldtype[ $field ][ 'scripts' ] ) ) {
				foreach ( $fieldtype[ $field ][ 'scripts' ] as $fieldscript ) {
					$jsfile = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/scripts/' . $fieldscript );
					foreach ( $groupconf as $varfield => &$value ) {
						$jsfile = str_replace( '{{' . $varfield . '}}', $value, $jsfile );
					}
					$wp_filesystem->put_contents( dirname( $path ) . '/assets/js/' . $fieldscript, $jsfile, FS_CHMOD_FILE );
				}
			}
			if ( !empty( $fieldtype[ $field ][ 'images' ] ) ) {
				foreach ( $fieldtype[ $field ][ 'images' ] as $fieldimage ) {
					$imagefile = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/images/' . $fieldimage );
					$wp_filesystem->put_contents( dirname( $path ) . '/assets/images/' . $fieldimage, $imagefile, FS_CHMOD_FILE );
				}
			}
			if( isset( $fieldtype[ $field ][ 'file' ] ) ){
				$fieldfile = $wp_filesystem->get_contents( BRICKROAD_PATH . '/templates/fields/' . $fieldtype[ $field ][ 'file' ] );
				foreach ( $groupconf as $varfield => &$value ) {
					$fieldfile = str_replace( '{{' . $varfield . '}}', $value, $fieldfile );
				}
				$wp_filesystem->put_contents( dirname( $path ) . '/includes/' . $fieldtype[ $field ][ 'file' ], $fieldfile, FS_CHMOD_FILE );
			}
		};
	}
	
	return $fieldtype;
}


function brickroad_register_element($name, $atts){
	global $registeredElements;
	if(!isset($atts['_slug'])){
		return;
	}else{
		global $registeredElements;
		$Elements = get_option('BR_ELEMENTS');
		foreach($Elements as $Element=>$Opt){
			if($Opt['shortcode'] == $atts['_slug'] && $Opt['state'] == 1){
				$shortcode = $Opt['shortcode'];
				break;
			}
		}
		if(empty($shortcode)){
			return;
		}
		$slug = $shortcode;
		$ID = $Element;
		$content = false;
		if(!empty($atts['_content'])){
			$content = $slugs[$atts['_content']];
			unset($atts['_content']);
		}
		unset($atts['_slug']);
		$atts = brickroad_getDefaultAtts($ID, $atts);
		brickroad_processHeaders($ID, $atts);
		$registeredElements[$name] = brickroad_doShortcode($atts['atts'], $content, $slug);
	}
}

function brickroad_render_element($name = false){
	global $registeredElements;
	if(isset($registeredElements[$name])){
		return $registeredElements[$name];
	}
	return;
}

if(is_admin()){

	

	function brickroad_zip($Source, $Destination, $removesource = false){
		if(file_exists($Destination)){
			unlink($Destination);
		}
		ini_set("max_execution_time", 300);
			// create object
		$zip = new ZipArchive();
			// open archive
		if ($zip->open($Destination, ZIPARCHIVE::CREATE) !== TRUE) {
			die ("Could not open archive");
		}
			// initialize an iterator
			// pass it the directory to be processed
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Source));
			// iterate over the directory
			// add each file found to the archive
		foreach ($iterator as $key=>$value) {
			$local = str_replace(dirname($Source).'/', '', $key);
			if(basename($key) === '.' || basename($key) === '..'){
				continue;
			}
			$zip->addFile($key, $local) or die ("ERROR: Could not add file: $key");
		}
		$zip->close();
		if(!empty($removesource)){
			brickroad_removedirectory($Source);
		}
			// http headers for zip downloads
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".basename($Destination)."\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($Destination));
		@readfile($Destination);
		unlink($Destination);
		return;
	}

	function brickroad_removedirectory($directory, $empty=FALSE) {
		if (substr($directory, -1) == '/') {
			$directory = substr($directory, 0, -1);
		}
		if (!file_exists($directory) || !is_dir($directory)) {
			return FALSE;
		} elseif (is_readable($directory)) {
			$handle = opendir($directory);
			while (FALSE !== ($item = readdir($handle))) {
				if ($item != '.' && $item != '..') {
					$path = $directory . '/' . $item;
					if (is_dir($path)) {
						brickroad_removedirectory($path);
					} else {
						unlink($path);
					}
				}
			}
			closedir($handle);
			if ($empty == FALSE) {
				if (!rmdir($directory)) {
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	function brickroad_write_template($path, $code, $shortcode, $currentElement = false, $pluginID = false){
		global $wp_filesystem;
		
		if(!file_exists(dirname($path))){
			$wp_filesystem->mkdir(dirname($path));
		}
		$file = explode('_', basename($path));
		$extension = array_pop($file);
		$path = dirname($path).'/'.implode('_', $file);

		if(file_exists($path.'.php')){
			$wp_filesystem->delete($path.'.php');
		}
		if(file_exists($path.'.'.$extension)){
			$wp_filesystem->delete($path.'.'.$extension);
		}
		
		if(!empty($code)){
			$preatts = brickroad_getDefaultAtts($shortcode, 'export');
			if(empty($currentElement)){
				$Element = brickroad_get_element($shortcode);
			}else{
				$Element = $currentElement;
			}
			if(empty($Element)){
				return;
			}			
			$variables = array();
			$groups = array();
			if(!empty($Element['_variable'])){
				foreach($Element['_variable'] as $varkey=>$variable){
					//
					$varkeys[$varkey] = $variable;
					$variables[$variable] = array(
						'key' 		=> $varkey,
						'type'		=> $Element['_type'][$varkey],
						'label'		=> $Element['_label'][$varkey],
						'loop'		=> $Element['_isMultiple'][$varkey],
						'group'		=> $Element['_group'][$varkey]
						);
				}
				$groups = array();
				foreach($variables as $variable=>$set){
					if(!empty($set['group'])){
						if(is_array($preatts[$variable])){
							foreach($preatts[$variable] as $key=>$value){
								$groups[$varkeys[$set['group']]][$key][$variable] = $value;
							}
						}
					}
				}
			}

			$atts = array();
			foreach ($preatts as $key => $value) {
				if(is_array($value)){
					$value = implode(', ', $value);
				}
				$atts[$key] = $value;
			}

			//$instanceID = brickroad_checkInstanceID('ce'.$Element['_shortcode'], 'footer');
			
			$post = array(
				"ID" 					=> "ID",
				"post_author" 			=> "post author",
				"post_date" 			=> "post date",
				"post_date_gmt" 		=> "post date_gmt",
				"post_content" 			=> "post content",
				"post_title" 			=> "post title",
				"post_excerpt" 			=> "post excerpt",
				"post_status" 			=> "post status",
				"comment_status"		=> "comment status",
				"ping_status" 			=> "ping status",
				"post_password" 		=> "post password",
				"post_name" 			=> "post name",
				"to_ping" 				=> "to ping",
				"pinged" 				=> "pinged",
				"post_modified" 		=> "post modified",
				"post_modified_gmt" 	=> "post modified_gmt",
				"post_content_filtered" => "post content_filtered",
				"post_parent" 			=> "post parent",
				"guid" 					=> "guid",
				"menu_order" 			=> "menu order",
				"post_type" 			=> "post type",
				"post_mime_type"		=> "post mime type",
				"comment_count" 		=> "comment count",
				"filter" 				=> "filter",
				"format_content"		=> "format content"
				);

			// IFS
			$pattern = '\[if(.*?)?\](?:(.+?))?';
			preg_match_all('/' . $pattern . '/s', $code, $ifs);
			if (!empty($ifs)){
				foreach($ifs[1] as $ifkey=>$if){
					parse_str(trim($if), $ifcases);
					$clauses = array();
					foreach($ifcases as $field=>$case){
						if(!empty($case)){
							$clauses[] = trim($case).' == $atts[\''.trim($field).'\']';
						}else{
							$clauses[] = 'isset($atts[\''.trim($field).'\'])';
						}
					}
					$code = preg_replace("/(".preg_quote('[if '.trim($if).']').")/m", '<?php if('.implode(' && ', $clauses).'){ ?>', $code,1);
				}

				$code = str_replace('[else]', "<?php }else{ ?>", $code);
				$code = str_replace('[/if]', "<?php } ?>", $code);
			}

			// LOOPS
			if(!empty($groups)){
				$pattern = '\[loop(.*?)?\](?:(.+?)?\[\/loop\])?';
				preg_match_all('/' . $pattern . '/s', $code, $loops);
				$loopblocks = array();
				if (!empty($loops)){
					foreach($loops[1] as $loopindex => $loop){
						$loopfield = trim($loop);
						$loopblock = $loops[2][$loopindex];
						if(!empty($groups[$loopfield])){
							foreach($groups[$loopfield] as $group){
								foreach($group as $field=>$value){
									$loopblock = str_replace('{{'.$field.'}}', '<?php echo $context[\''.$field.'\']; ?>', $loopblock);
									$loopblock = str_replace('{{_index_}}', '<?php echo $increment; ?>', $loopblock);
									$loopblock = str_replace('$atts', '$context', $loopblock);
								}
							}
						}
						$loopblock = '<?php foreach((array) $groups[\''.$loopfield.'\'] as $increment=>$context){ ?>'.$loopblock.'<?php } ?>';
						$code = str_replace($loops[0][$loopindex], $loopblock, $code);
					}
				}
			}
			// onces on css
			if(!empty($currentElement)){
				$check = '$this->element_css_once[\''.$Element['_shortcode'].'\']';
			}else{
				$check = '$css_once[\''.$Element['_shortcode'].'\']';
			}
			$once_open = '<?php if(!isset('.$check.')){ ?>';
			$once_close = '<?php '.$check.' = true; } ?>';
			$code = str_replace('[once]', $once_open, $code);
			$code = str_replace('[/once]', $once_close, $code);

			foreach($atts as $field=>$value){
				$code = str_replace('{{'.$field.'}}', '<?php echo implode(", ", (array)$atts[\''.$field.'\']); ?>', $code);	
			}
			
			$code = str_replace('{{content}}', '<?php if(isset($content)){ echo $content;} ?>', $code);
			$code = str_replace('{{_id_}}','<?php echo $instanceID; ?>', $code);

			foreach ($post as $varkey => &$value) {
				$code = str_replace('{{'.$varkey.'}}', "<?php if( isset( \$raw_atts['id'] ) ){ echo get_post_field('".$varkey."', \$raw_atts['id'] ); } ?>", $code);
				$Element['_javascriptCode'] = str_replace('{{'.$varkey.'}}',"<?php if( isset( \$raw_atts['id'] ) ){ echo get_post_field('".$varkey."', \$raw_atts['id'] ); } ?>", $Element['_javascriptCode']);
			}
			
			if(!empty($Element['_assetLabel'])){
				foreach($Element['_assetLabel'] as $assetKey=>$AssetLabel){
					$code = str_replace('{{'.$AssetLabel.'}}', '<?php echo $assets[\''.$AssetLabel.'\']; ?>', $code);
				}
			}
			
			if(!empty($templates)){
				foreach($templates as $altshortcode=>$conf){
					$insertTypes = array(5,7);
					$isSide = brickroad_get_element($altshortcode);
					if(in_array($isSide['_elementType'], $insertTypes)){
						if(false !== strpos($code, '{{:'.$isSide['_shortcode'].':}}')){
							$insertContent = '<?php echo '.$pluginID.'_doShortcode('.$pluginID.'_get_settings(\''.$altshortcode.'\'), \'\', \''.$altshortcode.'\', true); ?>';
							$code = str_replace('{{:'.$isSide['_shortcode'].':}}', $insertContent, $code);
						}
					}
				}
			}
			
			// quick check if static or dynamic
			preg_match_all('/<\?[=|php]?[\s\S]*?\?>/', $code, $phptags);
			if(!empty($phptags[0])){
				$extension = 'php';
			}


			$wp_filesystem->put_contents( $path.'.'.$extension, $code, FS_CHMOD_FILE);
		}

	}

}

?>