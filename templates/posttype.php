<?php
	
	/**
	 * Register post types.
	 *
	 *
	 * @return    null
	 */
	public function activate_post_types() {
		{{register_post_types}}
		{{post_type_metabox}}
		{{post_type_columns}}
		add_filter( 'post_updated_messages', array($this, 'updated_messages') );
	}

	/**
	 * setup post type messages.
	 *
	 *
	 * @return    array
	 */
	function updated_messages( $messages ) {
	  global $post, $post_ID;

	  {{post_messages}}

	  return $messages;
	}

	{{meta_boxes}}
	/**
	 * setup meta boxes.
	 *
	 *
	 * @return    null
	 */

	{{custom_columns}}