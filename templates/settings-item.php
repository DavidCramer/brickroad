<?php
	

            $this->plugin_screen_hook_suffix['{{_settingsSlug}}'] =  add_submenu_page( '{{_settingsParent}}', __( '{{_pageTitle}}', $this->plugin_slug ), __( '{{_pageTitle}}', $this->plugin_slug ), 'manage_options', '{{_settingsSlug}}', array( $this, 'create_admin_page' ) );
			add_action( 'admin_print_styles-' . $this->plugin_screen_hook_suffix['{{_settingsSlug}}'], array( $this, 'enqueue_admin_stylescripts' ) );

