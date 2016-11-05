<?php
		//neeeto!
		$posts = get_posts(array('post_type' => '{{_widgetSlug}}','posts_per_page' => -1));
		echo "<p><select class=\"widefat\" name=\"".self::get_field_name('id')."\" id=\"".self::get_field_id('id')."\">\r\n";
		if(empty($posts)){
			echo '<option value="">'.__('No items available','{{slug}}').'</option>';
		}else{
			echo '<option value=""></option>';
			foreach($posts as $post){
				$sel = "";
				if(!empty($instance['id'])){
					if( $instance['id'] == $post->ID ){
						$sel = 'selected="slected"';
					}
				}
				echo '<option value="'.$post->ID.'" '.$sel.'> '.$post->post_title.'</option>'."\r\n";
			}
		}
		echo "<select></p>\r\n";