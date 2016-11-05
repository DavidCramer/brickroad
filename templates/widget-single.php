<?php
		// Display the admin form
		//$configfiles = glob( self::get_path( dirname( __FILE__ ) ) .'configs/{{_widgetSlug}}-*.php' );
		if(file_exists(self::get_path( dirname( __FILE__ ) ) .'configs/fieldgroups-{{_widgetSlug}}.php')){
			include self::get_path( dirname( __FILE__ ) ) .'configs/fieldgroups-{{_widgetSlug}}.php';		
		}else{
			return;
		}

		echo "<input type=\"hidden\" name=\"{{key}}-widget\">\r\n";
		$groups = array();
		$setsize = 'full';

		{{widget_has_title}}
		echo "<div style=\"position: relative;\">\r\n";

		foreach ($configfiles as $key=>$fieldfile) {
			include $fieldfile;
			$group['id'] = uniqid('{{slug}}');
			$groups[] = $group;
		}
		{{widget_has_nav}}
			
			echo "<div class=\"{{slug}}-widget-config-content " . $setsize . "\">\r\n";
			foreach ($groups as $key=>$group) {
				echo "<div id=\"row".self::get_field_id('__row'.$group['id'])."\" class=\"{{slug}}-groupbox group\" " . ( !empty($instance['__cur_tab__']) ? ($instance['__cur_tab__'] == $key ? "" : "style=\"display:none;\"") : ($key === 0 ? "" : "style=\"display:none;\"" )) . ">\r\n";
				if(count($groups) > 1 || empty($setsize)){
					echo "<h3>".$group['label']."</h3>";
				}
				$this->group($group, $instance);
				echo "</div>\r\n";
			}
			{{widget_content}}
			echo '</div>';
		echo "</div>\r\n";
		echo "<hr class=\"widget-footer\">\r\n";