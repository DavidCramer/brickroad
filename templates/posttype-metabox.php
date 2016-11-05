<?php
	/**
	 * setup meta boxes.
	 *
	 *
	 * @return    null
	 */
	function add_metabox($slug, $post){
		// Always good to have.
		wp_enqueue_media();
		wp_enqueue_script('media-upload');
		
		wp_enqueue_style( $this->plugin_slug . '-panel-styles', self::get_url( 'assets/css/panel.css', __FILE__ ), array(), self::VERSION );
		wp_enqueue_script( $this->plugin_slug . '-panel-script', self::get_url( 'assets/js/panel.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		{{add_meta_boxes}}
	}

	/**
	 * render meta boxes.
	 *
	 *
	 * @return    null
	 */
	function render_metabox($post, $args){
		// include the metabox view
		echo '<input type="hidden" name="{{key}}_metabox" id="{{key}}_metabox" value="'.wp_create_nonce(plugin_basename(__FILE__)).'" />';
		echo '<input type="hidden" name="{{key}}_metabox_prefix[]" value="'.$args['args']['slug'].'" />';

		include self::get_path( __FILE__ ) . 'configs/' . $post->post_type . '-' . $args['args']['file'] . '.php';
		
		echo "<div class=\"group {{slug}}-row-sorter\" id=\"rowitems\">\r\n";
		// build instance
		$depth = 1;
		$instance = get_post_meta($post->ID, $args['args']['slug'], true);
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

			echo "<table class=\"form-table rowGroup groupitems " . ( !empty($group['multiple']) ? 'group-multiple row-sorter' : '' ) . "\" id=\"groupitems_".$i."\" ref=\"items\">\r\n";
			echo "	<tbody>\r\n";
				foreach($group['fields'] as $field=>$settings){
					//dump($settings);
					$id = 'field_'.$field;
					$groupid = $args['id'];
					$name = $args['args']['slug'].'['.$field.']';
					$single = true;
					$value = $settings['default'];
					if(!empty($group['multiple'])){
						$name = $args['args']['slug'].'['.$field.'][]';
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
			echo "<div class=\"toolrow\"><button class=\"button {{slug}}-add-group-row\" type=\"button\" data-rowtemplate=\"group-".$args['id']."-tmpl\">".__('Add Another', '{{slug}}')."</button></div>\r\n";
		}
		echo "</div>\r\n";
		// Place html template for repeated fields.
		if(!empty($group['multiple'])){
			echo "<script type=\"text/html\" id=\"group-".$args['id']."-tmpl\">\r\n";
			echo '  <div class="button button-small right {{slug}}-removeRow" style="margin-top:5px;">'.__('Remove', '{{slug}}').'</div>';
			echo "	<table class=\"form-table rowGroup groupitems " . ( !empty($group['multiple']) ? 'group-multiple row-sorter' : '' ) . "\" id=\"groupitems\" ref=\"items\">\r\n";
			echo "		<tbody>\r\n";
				foreach($group['fields'] as $field=>$settings){
					//dump($settings);
					$id = 'field_{{id}}';
					$groupid = $args['id'];
					$name = $args['args']['slug'].'['.$field.']';
					$single = true;
					if(!empty($group['multiple'])){
						$name = $args['args']['slug'].'['.$field.'][]';
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

