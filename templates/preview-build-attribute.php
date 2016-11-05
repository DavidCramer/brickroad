<?php

	/**
	 * render attribute based meta boxes.
	 *
	 *
	 * @return    null
	 */
	function render_attributes_panel($slug, $attribute_groups){

		$out = array(); 

		$panel_id = "preview-attributes";
		// include the metabox view

		ob_start();
		
		if(file_exists(self::get_path( __FILE__ ) .'configs/fieldgroups-'.$slug.'.php')){
			include self::get_path( __FILE__ ) .'configs/fieldgroups-'.$slug.'.php';
			if( !empty( $configfiles ) ) {

				foreach ($configfiles as $key=>$fieldfile) {
					include $fieldfile;
					if(!empty($group['scripts'])){
						foreach($group['scripts'] as $script){
							if( is_array( $script ) ){
								foreach($script as $remote=>$location){
									$infoot = false;
									if($location == 'footer'){
										$infoot = true;
									}
									if( false !== strpos($remote, '.')){
										$out['script'][sanitize_key($remote)] = $remote;
									}else{
										// need to get this
										//$out['script'][] = $remote;
									}
								}
							}else{
								if( false !== strpos($script, '.')){
									$out['script'][sanitize_key($script)] = self::get_url( 'assets/js/'.$script , __FILE__ );
								}else{
									$out['script'][sanitize_key($script)] = $script;
								}
							}
						}
					}
					if(!empty($group['styles'])){
						foreach($group['styles'] as $style){
							if( is_array( $style ) ){
								foreach($style as $remote){
									$out['style'][sanitize_key($remote)] = $remote;
								}
							}else{
								$out['style'][sanitize_key($style)] = self::get_url( 'assets/css/'.$style , __FILE__ );
							}
						}
					}
				}
			}
		}

		// setup tabbing if any
		$current_tab = 0;
		$panelsetsize = 'full';
		foreach($attribute_groups as $group){
			if(!file_exists( self::get_path( __FILE__ ) . 'configs/' . $slug . '-' . strtolower( $group ) . '.php' ) )
				continue;

			include self::get_path( __FILE__ ) . 'configs/' . $slug . '-' . strtolower( $group ) . '.php';
			$group['id'] = uniqid('{{slug}}');
			$groups[] = $group;
		}
		// no groups so exit
		if(empty($groups)){
			return;
		}

		$instance = get_transient("_".$slug."_preview");		

	
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
		{{preview_has_content}}
		echo "	</ul>\r\n";
		echo "</div>\r\n";
		$panelsetsize = null;
	
		// build instance
		foreach ($groups as $key=>$group) {
			echo "<div class=\"{{slug}}-metabox-config-content " . $panelsetsize . " group\" id=\"".$group['id']."\" ". ( $current_tab == $key ? '' : 'style="display:none;"' ) .">\r\n";

			if(count($groups) > 1 || empty($panelsetsize)){
				echo "<h3 class=\"metaheader\">".$group['label']."</h3>";
			}			
			
			$depth = 1;
			if(!empty($group['multiple'])){
				foreach($group['fields'] as $field=>$settings){
					if(isset($instance[$field])){
						if(count($instance[$field]) > $depth){
							$depth = count($instance[$field]);
						}
					}
				}
			}
			for( $i=0; $i<$depth;$i++ ){
				
				if($i > 0){
					echo '  <div class="button button-small right {{slug}}-removeRow" style="margin-top:5px;">'.__('Remove', '{{slug}}').'</div>';
				}

				echo "<table class=\"form-table rowGroup groupitems\" ref=\"items\" >\r\n";
				echo "	<tbody>\r\n";
					foreach($group['fields'] as $field=>$settings){
						//dump($settings);
						$id = 'field_'.$field;
						$groupid = $panel_id;
						$name = $slug.'['.$field.']';
						$single = true;
						$value = $settings['default'];
						if(!empty($group['multiple'])){
							$name = $slug.'['.$field.']['.$i.']';
							if(isset($instance[$field][$i])){
								$value = $instance[$field][$i];
							}						
						}else{
							if(isset($instance[$field])){
								$value = $instance[$field];
							}
						}
						$label = $settings['label'];
						$caption = $settings['caption'];
						echo "<tr valign=\"top\">\r\n";
							echo "<th scope=\"row\">\r\n";
								echo "<label for=\"".$id."\">".$label."</label>\r\n";
							echo "</th>\r\n";
							echo "<td>\r\n";					
								include self::get_path( __FILE__ ) . 'includes/field-'.$settings['type'].'.php';
								if(!empty($caption)){
									echo '<p class="description">'.$caption.'</p>';
								}
							echo "</td>\r\n";
						echo "</tr>\r\n";

					}
				echo "	</tbody>\r\n";
				echo "</table>\r\n";
			}
			if(!empty($group['multiple'])){
				echo "<div class=\"toolrow\"><button class=\"button {{slug}}-add-group-row\" type=\"button\" data-rowtemplate=\"group-".$panel_id."-tmpl\">".__('Add Another', '{{slug}}')."</button></div>\r\n";
			}
			echo "</div>\r\n";
			// Place html template for repeated fields.
			if(!empty($group['multiple'])){
				echo "<script type=\"text/html\" id=\"group-".$panel_id."-tmpl\">\r\n";
				echo '  <div class="button button-small right {{slug}}-removeRow" style="margin-top:5px;">'.__('Remove', '{{slug}}').'</div>';
				echo "	<table class=\"form-table rowGroup groupitems\" id=\"groupitems\" ref=\"items\">\r\n";
				echo "		<tbody>\r\n";
					foreach($group['fields'] as $field=>$settings){
						//dump($settings);
						$id = 'field_{{id}}';
						$groupid = $panel_id;
						$name = $slug.'['.$field.']';
						$single = true;
						if(!empty($group['multiple'])){
							$name = $slug.'['.$field.'][__count__]';
						}
						$label = $settings['label'];
						$caption = $settings['caption'];
						$value = $settings['default'];
						echo "<tr valign=\"top\">\r\n";
							echo "<th scope=\"row\">\r\n";
								echo "<label for=\"".$id."\">".$label."</label>\r\n";
							echo "</th>\r\n";
							echo "<td>\r\n";

								include self::get_path( __FILE__ ) . 'includes/field-'.$settings['type'].'.php';
								if(!empty($caption)){
									echo '<p class="description">'.$caption.'</p>';
								}

							echo "</td>\r\n";
						echo "</tr>\r\n";

					}
				echo "		</tbody>\r\n";
				echo "	</table>\r\n";	
				echo "</script>";
			}
		}
		{{preview_content}}
		$out['html'] = ob_get_clean();
	return $out;
	}