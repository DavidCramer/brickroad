<?php
	
				// is this a post type ID?
				if(!empty($atts['id'])){
					$content = get_post_field('post_content', $atts['id']);
					$atts = get_post_meta($atts['id'], $slug, true);
					//if(is_array($value)){
					//	foreach($value as &$varval){
					//		$varval = $this->process_value($conf['type'],$varval);
					//	}
					//}
				}