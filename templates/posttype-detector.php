<?php
	
		// is this a post type?
		if(isset($this->elements['posttypes'])){
			$this_post_type = get_post_type();
			if(isset($this->elements['posttypes'][$this_post_type])){
				if($this->elements['posttypes'][$this_post_type] === 'browsable'){
					// Browseable - render element over content.
					foreach ($wp_query->posts as $key => &$post) {
						// process header portion
						$this->render_element(array('id'=>$post->ID), $post->post_content, $this_post_type, true);
						// render content portion and replace content
						$post->post_content = $this->render_element(array('id'=>$post->ID), $post->post_content, $this_post_type);
					}
				}
			}
		}
