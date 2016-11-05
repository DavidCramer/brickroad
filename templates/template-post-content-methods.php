<?php
	/**
	 * filter post content with template
	 *
	 *
	 * @return    null
	 */

	public function use_content_template( $content ) {
		global $post;
		
		/// Post types for template
		{{templates}}

		// no template for this type - return default
		return $content;
	}	