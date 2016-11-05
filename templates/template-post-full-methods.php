<?php
	/**
	 * switch post type template.
	 *
	 *
	 * @return    null
	 */

	public function use_post_template( $template ) {
		global $post;
		
		/// Post types for template
		{{templates}}

		// no template for this type - return default
		return $template;
	}	