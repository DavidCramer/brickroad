<?php

	/***
	 * Get the current URL
	 *
	 */
	static function get_url($src = null, $path = null) {

		if(!empty($path)){
			return plugins_url( $src, __FILE__ );
		}
		return trailingslashit( plugins_url( $path , __FILE__ ) );
	}

	/***
	 * Get the current URL
	 *
	 */
	static function get_path($src = null) {
		return plugin_dir_path( $src );

	}