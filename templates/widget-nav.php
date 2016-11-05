<?php
			echo "<input type=\"hidden\" name=\"".self::get_field_name('__cur_tab__')."\" id=\"".self::get_field_id('__cur_tab__')."\" value=\"".(!empty($instance['__cur_tab__']) ? $instance['__cur_tab__'] : 0)."\">";
			echo "<div class=\"{{slug}}-widget-config-nav\">\r\n";
			echo "	<ul>\r\n";
			foreach ($groups as $key=>$group) {
					echo "		<li class=\"" . ( !empty($instance['__cur_tab__']) ? ($instance['__cur_tab__'] == $key ? "current" : "") : ($key === 0 ? "current" : "" )) . "\">\r\n";
					echo "			<a data-tabkey=\"".$key."\" data-tabset=\"".self::get_field_id('__cur_tab__')."\" title=\"".$group['label']."\" href=\"#row".self::get_field_id('__row'.$group['id'])."\"><strong>".$group['label']."</strong></a>\r\n";
					echo "		</li>\r\n";
			}
			{{widget_has_content}}
			echo "	</ul>\r\n";
			echo "</div>\r\n";
			$setsize = null;