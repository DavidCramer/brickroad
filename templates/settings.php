<?php
class Settings_{{_pluginClass}} extends {{_pluginClass}}{


	/**
	 * Start up
	 */
	public function __construct(){
		add_action( 'admin_menu', array( $this, 'add_settings_pages' ), 25 );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_settings_pages(){
		// This page will be under "Settings"
		{{settings_items}}
	}


	/**
	 * Options page callback
	 */
	public function create_admin_page(){
		// Set class property        
		$screen = get_current_screen();
		$base = array_search($screen->id, $this->plugin_screen_hook_suffix);
			// Display the admin form  
			//$configfiles = glob( self::get_path( dirname( __FILE__ ) ) .'configs/' . $base . '-*.php' );
		$slug = "_" . $base . "_options";
		$instance = get_option( $slug );

			if(file_exists(self::get_path( dirname( __FILE__ ) ) .'configs/fieldgroups-'.$base.'.php')){
				include self::get_path( __FILE__ ) . 'settings-' . $base . '.php';
				include self::get_path( dirname( __FILE__ ) ) .'configs/fieldgroups-'.$base.'.php';		

				$groups = array();

				foreach ($configfiles as $key=>$fieldfile) {
					include $fieldfile;
					$group['id'] = uniqid( $base );
					$groups[] = $group;
				}
				echo "<input type=\"hidden\" name=\"".$slug."[__cur_tab__]\" id=\"__cur_tab__\" value=\"".(!empty($instance['__cur_tab__']) ? $instance['__cur_tab__'] : 0)."\">";
				$setheight = null;
				if(count($groups) > 1){
					echo "<div class=\"{{slug}}-settings-config-nav\">\r\n";
					echo "	<ul>\r\n";
						foreach ($groups as $key=>$group) {
							echo "		<li class=\"" . ( !empty($instance['__cur_tab__']) ? ($instance['__cur_tab__'] == $key ? "current" : "") : ($key === 0 ? "current" : "" )) . "\">\r\n";
							echo "			<a data-tabkey=\"".$key."\" data-tabset=\"__cur_tab__\" title=\"".$group['label']."\" href=\"#row".$group['id']."\"><strong>".$group['label']."</strong></a>\r\n";
							echo "		</li>\r\n";
						}
					echo "	</ul>\r\n";
					echo "</div>\r\n";

					$setheight = "style=\"min-height: " . ( count($groups) * 31 + 32 ) . "px;\"";
				}
				echo "<div class=\"{{slug}}-settings-config-content " . ( count($groups) > 1 ? "" : "full" ) . "\" ".$setheight.">\r\n";
				foreach ($groups as $key=>$group) {	
					echo "<div id=\"row".$group['id']."\" class=\"{{slug}}-groupbox group\" " . ( !empty($instance['__cur_tab__']) ? ($instance['__cur_tab__'] == $key ? "" : "style=\"display:none;\"") : ($key === 0 ? "" : "style=\"display:none;\"" )) . ">\r\n";
					if(count($groups) > 1){
						echo "<h3>".$group['label']."</h3>";
					}				
					$this->settings_group($group, $instance, "_" . $base . "_options");
					echo "</div>\r\n";
				}
				echo "</div>\r\n";				

			}else{

				{{build_structures}}
				
				if(file_exists(self::get_path( dirname( __FILE__ ) ) .'includes/element-'.$base.'.php')){
					include self::get_path( dirname( __FILE__ ) ) .'includes/element-'.$base.'.php';
				}elseif(file_exists(self::get_path( dirname( __FILE__ ) ) .'includes/element-'.$base.'.html')){
					include self::get_path( dirname( __FILE__ ) ) .'includes/element-'.$base.'.html';
				}
				// php based script include
				if(file_exists(self::get_path( dirname( __FILE__ ) ) .'assets/js/scripts-'.$base.'.php')){
					echo "<script type=\"text/javascript\">\r\n";
						include self::get_path( dirname( __FILE__ ) ) .'assets/js/scripts-'.$base.'.php';
					echo "</script>\r\n";
				}

			}

		if(!empty($do_structures)){
			echo "			<div style=\"clear:both;\">\r\n";
							submit_button( __('Save Changes', 'brickroad-elements') );
			echo "			</div>";
			echo "		</form>\r\n";
			echo "	</div>\r\n";
		}
	}

	/**
	 * Register and add settings
	 */
	public function page_init(){
		{{register_settings}}
	}

	/**
	 * Generates a group of fields for the settings page.
	 *
	 */
	// build instance
	public function settings_group($group, $instance, $slug){
		
		$depth = 1;

		foreach($group['fields'] as $field=>$settings){         
			if(!empty($instance[$field]) && !empty($group['multiple'])){
				if(count($instance[$field]) > $depth){
					$depth = count($instance[$field]);
				}
			}
		}

		for( $i=0; $i<$depth;$i++ ){
				if($i > 0){
					echo '  <div class="button button-small right {{slug}}-removeRow" style="margin:5px 5px 0;">'.__('Remove', '{{slug}}').'</div>';
				}           
			echo "<div class=\"form-table rowGroup groupitems\" id=\"groupitems\" ref=\"items\">\r\n";
				foreach($group['fields'] as $field=>$settings){
					$id = 'field_'.$field.'_'.$i;
					$groupid = $group['id'];
					$name = $slug . '[' . $field . ']';
					$single = true;
					$value = $settings['default'];
					if(!empty($group['multiple'])){
						$name = $slug . '[' . $field . ']['.$i.']';
						if(isset($instance[$field][$i])){
							$value = $this->sanitize($instance[$field][$i]);
						}
					}else{
						if(isset($instance[$field])){
							$value = $this->sanitize($instance[$field]);
						}
					}
					$label = $settings['label'];
					$caption = (!empty($settings['caption']) ? $settings['caption'] : null);
					
					echo '<div class="{{slug}}-field-row"><label class="{{slug}}_settings_label" for="'.$id.'">'.$label.'</label>';
					include self::get_path( dirname( __FILE__ ) ) . 'includes/field-'.$settings['type'].'.php';
					if(!empty($caption)){
						echo '<p class="description">'.$caption.'</p>';
					}
					echo '</div>';

				}
			echo "</div>\r\n";
		}
		if(!empty($group['multiple'])){
			echo "<div class=\"{{slug}}-addRow\"><button class=\"button {{slug}}-add-group-row\" type=\"button\" data-field=\"ref-".$group['id']."\" data-rowtemplate=\"group-".$group['id']."-tmpl\">".__('Add Another', '{{slug}}')."</button></div>\r\n";
		}
		
		// Place html template for repeated fields.
		if(!empty($group['multiple'])){
			echo "<script type=\"text/html\" id=\"group-".$group['id']."-tmpl\">\r\n";
			echo '  <div class="button button-small right {{slug}}-removeRow" style="margin:5px 5px 0;">'.__('Remove', '{{slug}}').'</div>';
			echo "  <div class=\"form-table rowGroup groupitems\" id=\"groupitems\" ref=\"items\">\r\n";
				foreach($group['fields'] as $field=>$settings){
					//dump($settings);
					$id = 'field_{{id}}_'.$field;
					$groupid = $group['id'];
					$name = $slug . '[' . $field . ']';
					$single = true;
					if(!empty($group['multiple'])){
						$name = $slug . '[' . $field . '][__count__]';
					}
					$label = $settings['label'];
					$caption = (!empty($settings['caption']) ? $settings['caption'] : null);
					$value = $settings['default'];
					echo '<div class="{{slug}}-field-row"><label class="{{slug}}_settings_label" for="'.$id.'">'.$label.'</label>';
					include self::get_path( dirname( __FILE__ ) ) . 'includes/field-'.$settings['type'].'.php';
					if(!empty($caption)){
						echo '<p class="description">'.$caption.'</p>';
					}
					echo '</div>';
				}
			echo "  </div>\r\n";
			echo "</script>";
		}
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ){

		if( is_array( $input )){
			foreach ($input as &$value) {
				$value = htmlentities($value);
			}
		}else{
			$input = htmlentities($input);
		}
		return $input;
	}

	{{get_url_path}}

}

if( is_admin() )
	$settings_{{key}} = new Settings_{{key}}();
