<?php		
		if( count( $groups ) > 1){
			if(!empty($post)){
				$current_tab = get_post_meta($post->ID, '{{key}}__cur_tab__', true);
			}else{
				$current_tab = get_option('{{key}}__cur_tab__');
			}

			if(empty($current_tab)){
				$current_tab = 0;
			}
			echo "<input type=\"hidden\" name=\"{{key}}[__cur_tab__]\" id=\"{{key}}__cur_tab__\" value=\"".(!empty($current_tab) ? $current_tab : 0)."\">";
			echo "<div class=\"{{slug}}-metabox-config-nav\">\r\n";
			echo "	<ul>\r\n";
			foreach ($groups as $key=>$group) {
					echo "		<li class=\"" . ( !empty($current_tab) ? ($$current_tab == $key ? "current" : "") : ($key === 0 ? "current" : "" )) . "\">\r\n";
					echo "			<a data-tabkey=\"".$key."\" data-tabset=\"{{key}}__cur_tab__\" title=\"".$group['label']."\" href=\"#".$group['id']."\"><strong>".$group['label']."</strong></a>\r\n";
					echo "		</li>\r\n";
			}
			echo "	</ul>\r\n";
			echo "</div>\r\n";
			$panelsetsize = null;
		}