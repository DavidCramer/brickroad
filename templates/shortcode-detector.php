<?php
	
		// find used shortcodes within posts
		foreach ($wp_query->posts as $key => &$post) {
			$shortcodes = self::get_used_shortcodes($post->post_content);
			if(!empty($shortcodes[2])){
				foreach($shortcodes[2] as $foundkey=>$shortcode){
					$atts = array();
					if(!empty($shortcodes[3][$foundkey])){
						$atts = shortcode_parse_atts($shortcodes[3][$foundkey]);
					}
{{shortcodeheads}}					
					// process header portion
					$this->render_element($atts, $post->post_content, $shortcode, true);
				}
			}
		}
