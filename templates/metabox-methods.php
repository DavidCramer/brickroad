<?php

	/**
	 * setup meta boxes.
	 *
	 *
	 * @return    null
	 */
	function add_metaboxes( $slug, $post = false ){
		// Always good to have.
		wp_enqueue_media();
		wp_enqueue_script('media-upload');
		
		{{switch_types}}
		wp_enqueue_style( $this->plugin_slug . '-panel-styles', self::get_url( 'assets/css/panel.css', __FILE__ ), array(), self::VERSION );
		wp_enqueue_script( $this->plugin_slug . '-panel-script', self::get_url( 'assets/js/panel.js', __FILE__ ), array( 'jquery' ), self::VERSION );

		{{add_meta_boxes}}
		//{{shortcode}}
		//{{key}}
		//{{slug}}
	}

