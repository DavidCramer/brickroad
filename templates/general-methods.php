<?php

	/**
	 * setup meta boxes.
	 *
	 *
	 * @return    null
	 */
	public function get_post_meta($id, $key = null, $single = false){
		
		if(!empty($key)){

			//$configfiles = glob(self::get_path( __FILE__ ) .'configs/*.php');
			if(file_exists(self::get_path( __FILE__ ) .'configs/fieldgroups-{{key}}.php')){
				include self::get_path( __FILE__ ) .'configs/fieldgroups-{{key}}.php';		
			}else{
				return;
			}

			$field_type = 'text';
			foreach( $configfiles as $config=>$file ){
				include $file;
				if(isset($group['fields'][$key]['type'])){
					$field_type = $group['fields'][$key]['type'];
					break;
				}
			}
			$key = '{{key}}_' . $key;
		}
		if( false === $single){
			$metas = get_post_meta( $id, $key );
			foreach ($metas as $key => &$value) {
				$value = $this->process_value( $field_type, $value );
			}
			return $metas;
		}
		return $this->process_value( $field_type, get_post_meta( $id, $key, $single ) );

	}


	/**
	 * save metabox data
	 *
	 *
	 */
	function save_post_metaboxes($pid, $post){

		if(!isset($_POST['{{key}}_metabox']) || !isset($_POST['{{key}}_metabox_prefix'])){return;}


		if(!wp_verify_nonce($_POST['{{key}}_metabox'], plugin_basename(__FILE__))){
			return $post->ID;
		}
		if(!current_user_can( 'edit_post', $post->ID)){
			return $post->ID;
		}
		if($post->post_type == 'revision' ){return;}
		
		foreach( $_POST['{{key}}_metabox_prefix'] as $prefix ){
			if(!isset($_POST[$prefix])){continue;}

			if(isset($_POST['{{key}}_storage' . $prefix]) && $_POST['{{key}}_storage' . $prefix] == 'single'){
				foreach($_POST[$prefix] as $field=>$data){
					update_post_meta($post->ID, $field, $data);
				}
			}else{
				delete_post_meta($post->ID, $prefix);
				add_post_meta($post->ID, $prefix, $_POST[$prefix]);
			}
			//foreach($_POST['{{key}}'] as $field=>$data){
			/*
			*/
			//}
		}
	}	