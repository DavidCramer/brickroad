<?php

	/**
	 * render attribute based meta boxes.
	 *
	 *
	 * @return    null
	 */
	function render_metaboxes($post, $args){
		// include the metabox view
		echo '<input type="hidden" name="{{key}}_metabox" id="{{key}}_metabox" value="'.wp_create_nonce(plugin_basename(__FILE__)).'" />';
		echo '<input type="hidden" name="{{key}}_metabox_prefix[]" value="'.$args['args']['slug'].'" />';
		echo '<input type="hidden" name="{{key}}_storage'.$args['args']['slug'].'" value="'.$args['args']['store'].'" />';

		// setup tabbing if any
		$current_tab = 0;
		$panelsetsize = 'full';
		foreach($args['args']['groups'] as $group){
			include self::get_path( __FILE__ ) . 'configs/' . $args['args']['slug'] . '-' . strtolower( $group ) . '.php';
			$group['id'] = uniqid('{{slug}}');
			$groups[] = $group;
		}
		// no groups so exit
		if(empty($groups)){
			return;
		}

		if(!empty($post)){
			if($args['args']['store'] == 'single'){
				$instance = array();
				foreach ($groups as $key=>$group) {
					foreach($group['fields'] as $field=>$settings){
						$instance[$field] = get_post_meta( $post->ID, $field, true );
					}
				}				
			}else{
				$instance = get_post_meta($post->ID, $args['args']['slug'], true);
			}
		}else{
			$instance = get_option($args['args']['slug']);
		}
		
		{{has_meta_nav}}
		// build instance
		foreach ($groups as $key=>$group) {
			//$minheight = "min-height: 100px;"
			if(count($groups) > 5){
				//$minheight = 
			}
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
						$groupid = $group['id'];
						$name = $args['args']['slug'].'['.$field.']';
						$single = true;
						$value = $settings['default'];
						if(!empty($group['multiple'])){
							$name = $args['args']['slug'].'['.$field.']['.$i.']';
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
				echo "<div class=\"toolrow\"><button class=\"button {{slug}}-add-group-row\" type=\"button\" data-rowtemplate=\"group-".$group['id']."-tmpl\">".__('Add Another', '{{slug}}')."</button></div>\r\n";
			}
			echo "</div>\r\n";
			// Place html template for repeated fields.
			if(!empty($group['multiple'])){
				echo "<script type=\"text/html\" id=\"group-".$group['id']."-tmpl\">\r\n";
				echo '  <div class="button button-small right {{slug}}-removeRow" style="margin-top:5px;">'.__('Remove', '{{slug}}').'</div>';
				echo "	<table class=\"form-table rowGroup groupitems\" id=\"groupitems\" ref=\"items\">\r\n";
				echo "		<tbody>\r\n";
					foreach($group['fields'] as $field=>$settings){
						//dump($settings);
						$id = 'field_{{id}}';
						$groupid = $group['id'];
						$name = $args['args']['slug'].'['.$field.']';
						$single = true;
						if(!empty($group['multiple'])){
							$name = $args['args']['slug'].'['.$field.'][__count__]';
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
	}