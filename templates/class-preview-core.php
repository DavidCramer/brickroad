<?php
/**
 * {{_pluginName}}.
 *
 * @package   {{_pluginClass}}
 * @author    {{_pluginAuthor}} <{{_pluginAuthorEmail}}>
 * @license   GPL-2.0+
 * @link      {{_pluginAuthorURI}}
 * @copyright {{_year}} {{_pluginAuthor}}
 */

/**
 * Plugin class.
 * @package {{_pluginClass}}
 * @author  {{_pluginAuthor}} <{{_pluginAuthorEmail}}>
 */
class {{_pluginClass}} {

	/**
	 * @var     string
	 */
	const VERSION = '{{_pluginVersion}}';
	/**
	 * @var      string
	 */
	protected $plugin_slug = '{{slug}}';
	/**
	 * @var      object
	 */
	protected static $instance = null;
	/**
	 * @var      array
	 */
	protected $element_instances = array();
	/**
	 * @var      array
	 */
	protected $element_css_once = array();
	/**
	 * @var      array
	 */
	protected $elements = array();

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 */
	private function __construct() {

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_stylescripts' ) );
		
		add_action('wp_footer', array( $this, 'footer_scripts' ) );

		{{inits}}
	}

	/**
	 * Return an instance of this class.
	 *
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 *
	 * @return    null
	 */
	public function enqueue_admin_stylescripts() {


		{{admin_styles}}

	}

	
	{{methods}}
	/**
	 * create and register an instance ID
	 *
	 */
	public function element_instance_id($id, $process){

		$this->element_instances[$id][$process][] = true;
		$count = count($this->element_instances[$id][$process]);
		if($count > 1){
			return $id.($count-1);
		}
		return $id;
	}

	/**
	 * Render the element
	 *
	 */
	public function render_element($atts, $content, $slug, $head = false) {
		$id = 'preview_field_name';
		$name = 'preview_field_id';
		$raw_atts = $atts;
		{{post_type_render_id}}

		if(!empty($head)){
			$instanceID = $this->element_instance_id('{{key}}'.$slug, 'header');
		}else{
			$instanceID = $this->element_instance_id('{{key}}'.$slug, 'footer');
		}

		//$configfiles = glob(self::get_path( __FILE__ ) .'configs/'.$slug.'-*.php');
		if(file_exists(self::get_path( __FILE__ ) .'configs/fieldgroups-'.$slug.'.php')){
			include self::get_path( __FILE__ ) .'configs/fieldgroups-'.$slug.'.php';		
		
			$defaults = array();
			foreach($configfiles as $file){

				include $file;
				foreach($group['fields'] as $variable=>$conf){
					if(!empty($group['multiple'])){
						$value = array($this->process_value($conf['type'],$conf['default']));
					}else{
						$value = $this->process_value($conf['type'],$conf['default']);
					}
					if(!empty($group['multiple'])){
						if(isset($atts[$variable.'_1'])){
							$index = 1;
							$value=array();
							while(isset($atts[$variable.'_'.$index])){
								$value[] = $this->process_value($conf['type'],$atts[$variable.'_'.$index]);
								$index++;
							}
						}elseif (isset($atts[$variable])) {
							if(is_array($atts[$variable])){
								foreach($atts[$variable] as &$varval){
									$varval = $this->process_value($conf['type'],$varval);
								}
								$value = $atts[$variable];
							}else{
								$value[] = $this->process_value($conf['type'],$atts[$variable]);
							}
						}
					}else{
						if(isset($atts[$variable])){
							$value = $this->process_value($conf['type'],$atts[$variable]);
						}
					}
					
					if(!empty($group['multiple']) && !empty($value)){
						foreach($value as $key=>$val){
							//if(is_array($val)){
								$groups[$group['master']][$key][$variable] = $val;
							//}elseif(strlen($val) > 0){
							//	$groups[$group['master']][$key][$variable] = $val;
							//}
						}
					}
					$defaults[$variable] = $value;
					/*if(is_array($value)){
						foreach($value as $varkey=>&$varval){
							if(is_array($val)){
								if(!empty($val)){
									unset($value[$varkey]);
								}
							}elseif(strlen($varval) === 0){
								unset($value[$varkey]);
							}
						}
						if(!empty($value)){
							$defaults[$variable] = implode(', ', $value);
						}
					}else{
						if(strlen($value) > 0){
							$defaults[$variable] = $value;
						}
					}*/
				}
			}
			//dump($atts,0);
			//dump($defaults,0);
			$atts = $defaults;
		}
		// pull in the assets
		$assets = array();
		if(file_exists(self::get_path( __FILE__ ) . 'assets/assets-'.$slug.'.php')){
			include self::get_path( __FILE__ ) . 'assets/assets-'.$slug.'.php';
		}

		ob_start();
		if(file_exists(self::get_path( __FILE__ ) . 'includes/element-'.$slug.'.php')){
			include self::get_path( __FILE__ ) . 'includes/element-'.$slug.'.php';
		}else if( file_exists(self::get_path( __FILE__ ) . 'includes/element-'.$slug.'.html')){
			include self::get_path( __FILE__ ) . 'includes/element-'.$slug.'.html';
		}
		$out = ob_get_clean();


		if(!empty($head)){

			// process headers - CSS
			if(file_exists(self::get_path( __FILE__ ) . 'assets/css/styles-'.$slug.'.php')){
				ob_start();
				include self::get_path( __FILE__ ) . 'assets/css/styles-'.$slug.'.php';
				$this->element_header_styles[] = ob_get_clean();
				add_action('wp_head', array( $this, 'header_styles' ) );
			}else if( file_exists(self::get_path( __FILE__ ) . 'assets/css/styles-'.$slug.'.css')){
				wp_enqueue_style( $this->plugin_slug . '-'.$slug.'-styles', self::get_url( 'assets/css/styles-'.$slug.'.css', __FILE__ ), array(), self::VERSION );
			}
			// process headers - JS
			if(file_exists(self::get_path( __FILE__ ) . 'assets/js/scripts-'.$slug.'.php')){
				ob_start();
				include self::get_path( __FILE__ ) . 'assets/js/scripts-'.$slug.'.php';				
				$this->element_footer_scripts[] = ob_get_clean();
			}else if( file_exists(self::get_path( __FILE__ ) . 'assets/js/scripts-'.$slug.'.js')){
				wp_enqueue_script( $this->plugin_slug . '-'.$slug.'-script', self::get_url( 'assets/js/scripts-'.$slug.'.js', __FILE__ ), array( 'jquery' ), self::VERSION , true );
			}
			// get clean do shortcode for header checking
			ob_start();
			do_shortcode( $out );
			ob_get_clean();			
			return;
		}
		
		// CHECK FOR EMBEDED ELEMENTS
		/*foreach($elements as $subelement){
			if(empty($subelement['state']) || $subelement['shortcode'] == $element['_shortcode']){continue;}
			if(false !== strpos($out, '{{:'.$subelement['shortcode'].':}}')){
				$out = str_replace('{{:'.$subelement['shortcode'].':}}', brickroad_doShortcode(array(), $out, $subelement['shortcode']), $out);
			}
		}*/


		/*if(!empty($element['_removelinebreaks'])){
			add_filter( 'the_content', 'wpautop' );
		}*/

		return do_shortcode($out);
	}

	/**
	 * Render any header styles
	 *
	 */
	public function header_styles() {
		if(!empty($this->element_header_styles)){
			echo "<style type=\"text/css\">\r\n";
			foreach($this->element_header_styles as $styles){
				echo $styles."\r\n";
			}			
			echo "</style>\r\n";
		}
	}
	
	/**
	 * Render any footer scripts
	 *
	 */
	public function footer_scripts() {

		if(!empty($this->element_footer_scripts)){
			echo "<script type=\"text/javascript\">\r\n";
				foreach($this->element_footer_scripts as $script){
					echo $script."\r\n";
				}
			echo "</script>\r\n";
		}
	}

	{{get_url_path}}
	
}
