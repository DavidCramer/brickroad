<?php

	/***
	 * Get the current URL
	 *
	 */
	static function get_url($src = null, $path = null) {
		if(!empty($path)){			
			return get_template_directory_uri() . '/framework/' . $src;
		}		
		return get_template_directory_uri() . '/framework/';

	}

	/***
	 * Get the current URL
	 *
	 */
	static function get_path($src = null) {
		return dirname( $src ) . '/';

	}
	