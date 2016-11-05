<?php

	/**
	 * render template based meta boxes.
	 *
	 *
	 * @return    null
	 */
	function render_metaboxes_custom($post, $args){
		// include the metabox view
		echo '<input type="hidden" name="{{key}}_metabox" id="{{key}}_metabox" value="'.wp_create_nonce(plugin_basename(__FILE__)).'" />';
		echo '<input type="hidden" name="{{key}}_metabox_prefix[]" value="'.$args['args']['slug'].'" />';

		//get post meta to $atts $ post content - ir the widget option
		if(!empty($post)){
			$atts = get_post_meta($post->ID, $args['args']['slug'], true);
			$content = $post->post_content;
		}else{
			$atts = get_option($args['args']['slug']);
			$content = '';
		}

		if(file_exists(self::get_path( __FILE__ ) . 'includes/element-' . $args['args']['slug'] . '.php')){
			include self::get_path( __FILE__ ) . 'includes/element-' . $args['args']['slug'] . '.php';
		}elseif(file_exists(self::get_path( __FILE__ ) . 'includes/element-' . $args['args']['slug'] . '.html')){
			include self::get_path( __FILE__ ) . 'includes/element-' . $args['args']['slug'] . '.html';
		}
		// add script
		if(file_exists(self::get_path( __FILE__ ) . 'assets/js/scripts-' . $args['args']['slug'] . '.php')){
			echo "<script type=\"text/javascript\">\r\n";
			include self::get_path( __FILE__ ) . 'assets/js/scripts-' . $args['args']['slug'] . '.php';
			echo "</script>\r\n";
		}elseif(file_exists(self::get_path( __FILE__ ) . 'assets/js/scripts-' . $args['args']['slug'] . '.js')){
			wp_enqueue_script( $this->plugin_slug . '-' . $args['args']['slug'] . '-script', self::get_url( 'assets/js/scripts-' . $args['args']['slug'] . '.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		}
		
	}