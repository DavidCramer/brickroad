<?php

class Widget_{{_widgetClass}} extends WP_Widget {

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		// load plugin text domain
		//add_action( 'init', array( $this, 'widget_textdomain' ) );

		parent::__construct(
			'{{_widgetSlug}}-id',
			__( '{{_widgetName}}', '{{slug}}' ),
			array(
				'description'	=>	__( '{{_widgetDesc}}.', '{{slug}}' )
			){{widget_panelsize}}
		);

		// Register admin styles and scripts
		add_action( 'admin_print_styles-widgets.php', array( $this, 'register_admin_styles_scripts' ) );

		// register front
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );

	} // end constructor

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @param	array	args		The array of form elements
	 * @param	array	instance	The current instance of the widget
	 */
	public function widget( $args, $instance ) {

		if( empty($instance) ){
			return;
		}

		if(isset($instance['__cur_tab__'])){
			unset($instance['__cur_tab__']);
		}
		extract( $args, EXTR_SKIP );

		{{widget_before}}
		{{widget_title}}
		//dump($instance);
		// TODO: Here is where you manipulate your widget's values based on their input fields
		$element = {{_pluginClass}}::get_instance();
		{{widget_render}}		

		{{widget_after}}

	} // end widget

	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param	array	new_instance	The new instance of values to be generated via the update.
	 * @param	array	old_instance	The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {

		return $new_instance;


	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param	array	instance	The array of keys and values for the widget.
	 */
	public function form( $instance ) {

    	// TODO:	Define default values for your variables
		$instance = wp_parse_args(
			(array) $instance
		);
		{{widget_type}}
	} // end form

	/**
	 * Generates a group of fields for the widget.
	 *
	 */
	// build instance
	public function group($group, $instance){
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
					//dump($settings);
					$id = self::get_field_id('field_'.$field).'_'.$i;
					$groupid = $group['id'];
					$name = self::get_field_name($field);
					$single = true;
					$value = $settings['default'];
					if(!empty($group['multiple'])){
						$name = self::get_field_name($field).'['.$i.']';
						if(isset($instance[$field][$i])){
							$value = $instance[$field][$i];
						}
					}else{
						if(isset($instance[$field])){
							$value = $instance[$field];
						}
					}
					$label = (!empty($settings['caption']) ? $settings['caption'] : $settings['label']);
					//$caption = $settings['caption'];
									
					echo '<div class="{{slug}}-field-row"><label class="{{slug}}_widget_label" for="'.$id.'">'.$label.'</label>';
					include self::get_path( dirname( __FILE__ ) ) . 'includes/field-'.$settings['type'].'.php';
					echo '</div>';
				}
			echo "</div>\r\n";
		}
		if(!empty($group['multiple'])){
			echo "<div><button class=\"button {{slug}}-add-group-row\" type=\"button\" data-field=\"".self::get_field_id('ref')."\" data-rowtemplate=\"group-".$group['id']."-tmpl\">".__('Add Another','{{slug}}')."</button></div>\r\n";
		}
		
		// Place html template for repeated fields.
		if(!empty($group['multiple'])){
			echo "<script type=\"text/html\" id=\"group-".$group['id']."-tmpl\">\r\n";
			echo '  <div class="button button-small right {{slug}}-removeRow" style="margin:5px 5px 0;">'.__('Remove','{{slug}}').'</div>';
			echo "	<div class=\"form-table rowGroup groupitems\" id=\"groupitems\" ref=\"items\">\r\n";
				foreach($group['fields'] as $field=>$settings){
					//dump($settings);
					$id = self::get_field_id('field_{{id}}_'.$field);
					$groupid = $group['id'];
					$name = self::get_field_name($field);
					$single = true;
					if(!empty($group['multiple'])){
						$name = self::get_field_name($field).'[__count__]';
					}
					$label = $settings['label'];
					$caption = $settings['caption'];
					$value = $settings['default'];
					echo '<div class="{{slug}}-field-row"><label class="{{slug}}_widget_label" for="'.$id.'">'.$label.'</label>';
					include self::get_path( dirname( __FILE__ ) ) . 'includes/field-'.$settings['type'].'.php';
					echo '</div>';
				}
			echo "	</div>\r\n";
			echo "</script>";
		}
	}

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function widget_textdomain() {

		load_plugin_textdomain( '{{slug}}', false, self::get_path( dirname( __FILE__ ) ) . '/lang/' );

	} // end widget_textdomain

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles_scripts() {

		// Always good to have.
		wp_enqueue_media();
		wp_enqueue_script('media-upload');

		//$configfiles = glob( self::get_path( dirname( __FILE__ ) ) .'configs/{{_widgetSlug}}-*.php' );
		if(file_exists(self::get_path( dirname( __FILE__ ) ) .'configs/fieldgroups-{{_widgetSlug}}.php')){
			include self::get_path( dirname( __FILE__ ) ) .'configs/fieldgroups-{{_widgetSlug}}.php';		
		}else{
			return;
		}
		foreach ($configfiles as $key=>$fieldfile) {
			include $fieldfile;
			if(!empty($group['scripts'])){
				foreach($group['scripts'] as $script){
					wp_enqueue_script( '{{slug}}-'.strtok($script, '.'), self::get_url( 'assets/js/'.$script , dirname(__FILE__) ) , array('jquery') );					
				}
			}
			if(!empty($group['styles'])){
				foreach($group['styles'] as $style){
					wp_enqueue_style( '{{slug}}-'.strtok($style, '.'), self::get_url( 'assets/css/'.$style , dirname(__FILE__) ) );
				}
			}
		}

		wp_enqueue_style( '{{slug}}-panel-styles', self::get_url( 'assets/css/panel.css', dirname(__FILE__) ) );
		wp_enqueue_script( '{{slug}}-panel-script', self::get_url( 'assets/js/panel.js', dirname(__FILE__) ), array( 'jquery' ) );


	} // end register_admin_styles
	
	/**
	* Registers and enqueues widget-specific styles.
	*/
	public function register_widget_styles() {
		if ( is_active_widget( false, false, $this->id_base, true ) ) {
			$widget_settings = get_option('widget_'.$this->id_base);
			$sidebars = get_option('sidebars_widgets');
			foreach ($sidebars as $sidebarid => $sidebar) {
				if(is_active_sidebar($sidebarid) && $sidebarid != 'wp_inactive_widgets' && false === strpos($sidebarid, 'orphaned_widgets')){

					foreach($sidebar as $instance){
						if( false !== strpos( $instance, $this->id_base ) ){
							$instance_id = (int) str_replace($this->id_base.'-', '', $instance);
							if(!empty($widget_settings[$instance_id])){
								$settings = $widget_settings[$instance_id];
								if(empty($element)){
									$element = {{_pluginClass}}::get_instance();
								}
								{{widget_headers}}
							}
						}
					}
				}
			}

		}

	} // end register_widget_styles

	{{get_url_path}}
	
} // end class

add_action( 'widgets_init', create_function( '', 'register_widget("Widget_{{_widgetClass}}");' ) );
