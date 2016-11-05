<?php
	
	/**
	 * Insert shortcode media button
	 *
	 *
	 */
	function shortcode_insert_button(){
		global $post;
		if(!empty($post)){
			echo "<a id=\"{{slug}}-shortcodeinsert\" title=\"".__('{{_pluginName}} Shortcode Builder','{{slug}}')."\"{{is_single_clean}} style=\"padding-left: 0.4em;\" class=\"button {{slug}}-editor-button\" href=\"#inst\">\n";
			echo "<span class=\"dashicons dashicons-text\"></span> ".__('{{_pluginName}}', '{{slug}}')."\n";
			echo "</a>\n";
		}
	}

	/**
	 * render shortcode config panel.
	 *
	 *
	 * @return    null
	 */
	function render_shortcode_panel($shortcode, $type = 1, $template = false){


		if(!empty($template)){
			echo "<script type=\"text/html\" id=\"{{slug}}-".$shortcode."-config-tmpl\">\r\n";
		}
		echo "<input id=\"{{slug}}-shortcodekey\" class=\"configexclude\" type=\"hidden\" value=\"".$shortcode."\">\r\n";
		echo "<input id=\"{{slug}}-shortcodetype\" class=\"configexclude\" type=\"hidden\" value=\"".$type."\">\r\n";
		echo "<input id=\"{{slug}}-default-content\" class=\"configexclude\" type=\"hidden\" value=\" ".__('Your content goes here','{{slug}}')." \">\r\n";

		if(!empty($this->elements['posttypes'][$shortcode])){
			$posts = get_posts(array('post_type' => $shortcode, 'posts_per_page' => -1));

			if(empty($posts)){
				echo 'No items available';
			}else{
				foreach($posts as $post){
					echo '<div class="posttype-item"><label><input type="radio" value="'.$post->ID.'" name="id"> '.$post->post_title.'</label></div>';
				}
			}
			if(!empty($template)){
				echo "</script>\r\n";
			}
			return;
		}
	
		if(file_exists(self::get_path( __FILE__ ) .'configs/fieldgroups-'.$shortcode.'.php')){
			include self::get_path( __FILE__ ) .'configs/fieldgroups-'.$shortcode.'.php';		
		}else{
			if(!empty($template)){
				echo "</script>\r\n";
			}
			return;
		}

		$groups = array();
		echo "<div class=\"{{slug}}-shortcode-config-nav\">\r\n";
		echo "	<ul>\r\n";
		foreach ($configfiles as $key=>$fieldfile) {
			include $fieldfile;
			$groups[] = $group;
				echo "		<li class=\"" . ( $key === 0 ? "current" : "" ) . "\">\r\n";
				echo "			<a title=\"".$group['label']."\" href=\"#row".$group['master']."\"><strong>".$group['label']."</strong></a>\r\n";
				echo "		</li>\r\n";
		}
		echo "	</ul>\r\n";
		echo "</div>\r\n";

		echo "<div class=\"{{slug}}-shortcode-config-content " . ( count($configfiles) > 1 ? "" : "full" ) . "\">\r\n";
			foreach($groups as $key=>$group){
				echo "<div class=\"group\" " . ( $key === 0 ? "" : "style=\"display:none;\"" ) . " id=\"row".$group['master']."\">\r\n";
				echo "<h3 class=\"{{slug}}-config-header\">".$group['label']."</h3>\r\n";
				echo "<table class=\"form-table rowGroup groupitems\" id=\"groupitems\" ref=\"items\">\r\n";
				echo "	<tbody>\r\n";
					foreach($group['fields'] as $field=>$settings){
						//dump($settings);
						$id = 'field_'.$field;
						$groupid = $group['id'];
						$name = $field;
						$single = true;
						if(!empty($group['multiple'])){
							$name = $field.'[]';
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
								echo "<p class=\"description\">".$caption."</p>\r\n";
							}
							echo "</td>\r\n";
						echo "</tr>\r\n";
					}
				echo "	</tbody>\r\n";
				echo "</table>\r\n";

				if(!empty($group['multiple'])){
					echo "<div class=\"toolrow\"><button class=\"button {{slug}}-add-group-row\" type=\"button\" data-rowtemplate=\"group-".$group['id']."-tmpl\">".__('Add Another','{{slug}}')."</button></div>\r\n";
				}
				echo "</div>\r\n";
			}
		echo "</div>\r\n";

		if(!empty($template)){
			echo "</script>\r\n";
		}
		// go get the loop templates
		foreach($groups as $group){
			// Place html template for repeated fields.
			if(!empty($group['multiple'])){
				echo "<script type=\"text/html\" id=\"group-".$group['id']."-tmpl\">\r\n";
				echo '  <div class="button button-small right {{slug}}-removeRow" style="margin:5px 5px 0;">'.__('Remove','{{slug}}').'</div>';
				echo "	<table class=\"form-table rowGroup groupitems\" id=\"groupitems\" ref=\"items\">\r\n";
				echo "		<tbody>\r\n";
					foreach($group['fields'] as $field=>$settings){
						//dump($settings);
						$id = 'field_{{id}}';
						$groupid = $group['id'];
						$name = $field.'[__count__]';
						$single = true;
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
								echo "<p class=\"description\">".$caption."</p>\r\n";
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

	/**
	 * Insert shortcode modal template
	 *
	 *
	 */
	function shortcode_modal_template(){
		$screen = get_current_screen();

		if($screen->base != 'post'){return;}

		echo "<script type=\"text/html\" id=\"{{slug}}-shortcode-panel-tmpl\">\r\n";
		echo "	<div tabindex=\"0\" id=\"{{slug}}-shortcode-panel\" class=\"hidden\">\r\n";
		echo "		<div class=\"media-modal-backdrop\"></div>\r\n";
		echo "		<div class=\"{{slug}}-modal-modal\">\r\n";
		echo "			<div class=\"{{slug}}-modal-content\">\r\n";
		echo "				<div class=\"{{slug}}-modal-header\">\r\n";
		echo "					<a title=\"Close\" href=\"#\" class=\"media-modal-close\">\r\n";
		echo "						<span class=\"media-modal-icon\"></span>\r\n";
		echo "					</a>\r\n";
		echo "					<h2><span class=\"dashicons dashicons-text\"></span> ".__('{{_pluginName}}','{{slug}}')." <small>".__("Shortcode Builder","{{slug}}")."</small></h2>\r\n";
		echo "				</div>\r\n";
		echo "				<div class=\"{{slug}}-modal-body\">\r\n";
		echo "					<span id=\"{{slug}}-categories\">\r\n";
		echo "						{{shortcode_selector}}\r\n";
		echo "					</span>\r\n";
		echo "					<div id=\"{{slug}}-shortcode-config\" class=\"{{slug}}-shortcode-config\">\r\n";
		echo "					</div>\r\n";
		echo "				</div>\r\n";
		echo "				<div class=\"{{slug}}-modal-footer\">\r\n";
		echo "					<button class=\"button button-primary button-large\" id=\"{{slug}}-insert-shortcode\">".__("Insert Shortcode","{{slug}}")."</button>\r\n";
		echo "				</div>\r\n";
		echo "			</div>\r\n";
		echo "		</div>\r\n";
		echo "	</div>\r\n";
		echo "</script>\r\n";

		foreach($this->elements['shortcodes'] as $shortcode=>$type){
			$this->render_shortcode_panel($shortcode, $type, true);
		}
		
	}

	/**
	 * Gets a list of shorcodes used within the content provided
	 *
	 * @return 	array
	 */
	function get_regex(){

	// this makes it easier to cycle through and get the used codes for inclusion
	$validcodes = join( '|', array_map('preg_quote', array_keys($this->elements['shortcodes'])) );
	return
			  '\\['                              // Opening bracket
			. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
			. "($validcodes)"                    // 2: selected codes only
			. '\\b'                              // Word boundary
			. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
			.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
			.     '(?:'
			.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
			.         '[^\\]\\/]*'               // Not a closing bracket or forward slash
			.     ')*?'
			. ')'
			. '(?:'
			.     '(\\/)'                        // 4: Self closing tag ...
			.     '\\]'                          // ... and closing bracket
			. '|'
			.     '\\]'                          // Closing bracket
			.     '(?:'
			.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
			.             '[^\\[]*+'             // Not an opening bracket
			.             '(?:'
			.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
			.                 '[^\\[]*+'         // Not an opening bracket
			.             ')*+'
			.         ')'
			.         '\\[\\/\\2\\]'             // Closing shortcode tag
			.     ')?'
			. ')'
			. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]

	}

	function get_used_shortcodes($content, $return = array(), $internal = true, $preview = false){

		$regex = self::get_regex();

		preg_match_all('/' . $regex . '/s', $content, $found);

		foreach($found[5] as $innerContent){
			if(!empty($innerContent)){
			   $new = self::get_used_shortcodes($innerContent, $found, $internal);
				if(!empty($new)){
					foreach($new as $key=>$val){
						$found[$key] = array_merge($found[$key], $val);
					}
				}
			}
		}

		return $found;
	}
