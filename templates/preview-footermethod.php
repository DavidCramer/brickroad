<?php
	
	/**
	 * Insert template into footer
	 *
	 *
	 */
	public function footer_template(){
		global $post;
		$posts = get_posts( array( 'posts_per_page' => 1 ) );
		if(!empty( $posts[0])){
			$post = $posts[0];
		}
		{{preview_footer}}
	}