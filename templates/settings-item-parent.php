<?php
	
			$this->plugin_screen_hook_suffix['{{_settingsSlug}}'] =  add_menu_page( __( '{{_pageTitle}}', $this->plugin_slug ), __( '{{_pageTitle}}', $this->plugin_slug ), 'manage_options', '{{_settingsSlug}}', array( $this, 'create_admin_page' ), '{{_settingsIcon}}' );
			add_action( 'admin_print_styles-' . $this->plugin_screen_hook_suffix['{{_settingsSlug}}'], array( $this, 'enqueue_admin_stylescripts' ) );

