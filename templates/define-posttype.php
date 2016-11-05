<?php
		$args = array(
			'labels' 				=> array(
				'name' 				=> __('{{name}}', '{{slug}}'),
				'singular_name' 	=> __('{{singular_name}}', '{{slug}}'),
				'add_new' 			=> __('{{add_new}}', '{{slug}}'),
				'add_new_item' 		=> __('{{add_new_item}}', '{{slug}}'),
				'edit_item' 		=> __('{{edit_item}}', '{{slug}}'),
				'all_items' 		=> __('{{all_items}}', '{{slug}}'),
				'view_item' 		=> __('{{view_item}}', '{{slug}}'),
				'search_items' 		=> __('{{search_items}}', '{{slug}}'),
				'not_found' 		=> __('{{not_found}}', '{{slug}}'),
				'not_found_in_trash'=> __('{{not_found_in_trash}}', '{{slug}}'),
				'parent_item_colon' => '{{parent_item_colon}}',
				'menu_name' 		=> __('{{menu_name}}', '{{slug}}')
			),
			'public' 				=>	{{public}},
			'publicly_queryable'	=>	{{publicly_queryable}},
			'show_ui' 				=>	true,
			'show_in_menu' 			=>	{{show_in_menu}},
			'query_var' 			=>	true,
			'rewrite' 				=>	{{rewrite}},
			'exclude_from_search' 	=>	false,
			'capability_type' 		=>	'post',
			'has_archive' 			=>	true,
			'hierarchical' 			=>	false,
			'menu_position' 		=>	{{_menuposition}},
			'menu_icon'				=>	{{_menuicon}},
			'supports' 				=> array(
				{{supports}}
			),
		);
		register_post_type('{{_shortcode}}', $args);