<?php

/*
 * Brickroad function library
 * (C) 2013 - David Cramer
 */

function brickroad_init() {
	// Load Post Types if Pro Version
	if(function_exists('brickroad_buildpost_types')){
	//	brickroad_buildpost_types();
	}
	
	$elements = get_option('BR_ELEMENTS');
	
	if(!is_admin()){
		// Build a preview in editor
		if(!empty($_GET['myshortcodeproinsert'])){
			$user = wp_get_current_user();
			if(in_array('administrator', $user->roles)){
				// NEED TO GET THIS PART OF AN AJAX BASED REQUEST.
				if($_GET['myshortcodeproinsert'] == 'preview'){
					include(BRICKROAD_PATH.'/libs/editorpreview.php');
					die;
				}
			}
		}
		//
		$urlpath = parse_url($_SERVER['REQUEST_URI']);
		$pathparts = array_reverse(explode('/', trim($urlpath['path'],'/')));
		if(count($pathparts) > 2){
			if($pathparts[1] == 'brickroad-embed-element'){
				return;
			}
		}

	}

}

function brickroad_get_settings($idcode){

	$element = brickroad_get_element($idcode);
	if($element['_elementType'] == 4 || $element['_elementType'] == 5 || $element['_elementType'] == 6){
		$settings = brickroad_get_element($element['_ID'].'_cfg');
		// MAKE AN ARRAY OF LOOPS
		if(isset($element['_isMultiple'])){
			foreach($element['_isMultiple'] as $key=>$is){
				if(!empty($is)){
					$i = 1;
					while(isset($settings[$element['_variable'][$key].'_'.$i])){
						$settings['_loop_groups'][$element['_variable'][$key]][$i] = $settings[$element['_variable'][$key].'_'.$i];
						$i++;
					}
				}
			}
		}
		return $settings;
	}
}

function brickroad_get_element($id){
	$element = get_option($id);
	if(is_array($element)){
		return $element;
	}
	$elements = get_option('BR_ELEMENTS');
	if(empty($elements)){return array();}
	foreach ($elements as $eid => $element) {
		if (!empty($element['shortcode'])) {
			if ($element['shortcode'] === $id) {
				return brickroad_get_element($eid);
			}
		}
	}
	return false;
}

function brickroad_getDefaultAtts($ElementID, $atts = false){

	$Element = brickroad_get_element($ElementID);
	if(!empty($Element['_variable'])){
		if(empty($Element['_elementType'])){
			$Element['_elementType'] = 1;
		}
		if($Element['_elementType'] == 4 || $Element['_elementType'] == 5){
			$defaultatts = brickroad_get_settings($ElementID);
			if(!empty($defaultatts) && !empty($atts)){
				$atts = shortcode_atts($defaultatts, $atts);
			}
		}
		$variables = array();
		$returnatts = array();
		foreach($Element['_variable'] as $varkey=>&$variable){
			//
			$variables[$variable] = array(
				'key' 		=> $varkey,
				'type'		=> $Element['_type'][$varkey],
				'label'		=> $Element['_label'][$varkey],
				'loop'		=> $Element['_isMultiple'][$varkey],
				'value'		=> (empty($Element['_isMultiple'][$varkey]) ? $Element['_variableDefault'][$varkey] : array(0=>$Element['_variableDefault'][$varkey]))
				);

			//if(isset($variables[$variable]['value'])){
			//	$variables[$variable]['value'] = $atts[$variable];
			//}
			if(!empty($Element['_isMultiple'][$varkey])){
				if(isset($atts[$variable.'_1'])){
					$multiindex = 1;
					while(isset($atts[$variable.'_'.$multiindex])){
						$variables[$variable]['value'][$multiindex-1] = $atts[$variable.'_'.$multiindex];
						$multiindex++;
					}
				}elseif(isset($atts[$variable])){
					$variables[$variable]['value'] = $atts[$variable];
				}
			}else{
				if(isset($atts[$variable])){
					$variables[$variable]['value'] = $atts[$variable];
				}
			}
			// Process the values with the field types
			// SWAP out when fields are addons
			if(!empty($variables[$variable]['value'])){
				$rawvalues = (array) $variables[$variable]['value'];
				foreach($rawvalues as $thiskey=>&$thisvalue){
					switch ($Element['_type'][$varkey]){
						case 'Dropdown':
							if(strpos($thisvalue, '*') !== false){
								$opts = explode(',', $thisvalue);
								foreach($opts as $valoption){
			                    	$keyval = explode('||', trim($valoption));
			                    	if(isset($keyval[1])){
			                    		$valoption = trim($keyval[0]);
			                    		$value = trim($keyval[1]);
			                    	}else{
			                    		$value = trim($valoption);
			                    	}							
									if(strpos($valoption, '*') !== false){
										$thisvalue = trim(strtok($valoption, '*'));
										break;
									}
								}
							}else{
								$thisvalue = trim($thisvalue);
							}
						break;
						case 'Custom':
							$p = explode(',',$thisvalue, 2);
							if(!empty($p[1])){
								$thisvalue = trim($p[1]);
							}
						break;
						case 'Image':
							$attachment = explode(',',$thisvalue);
							if(floatval($attachment[0]) > 0){
								if(empty($attachment[1])){
									$attachment[1] = 'medium';
								}
								$image = wp_get_attachment_image_src($attachment[0], $attachment[1]);
								$thisvalue = $image[0];
							}

						break;
						case 'File':
							if(floatval($thisvalue) > 0){
								$thisvalue = wp_get_attachment_url($thisvalue);
							}

						break;

					}
				}
			}
			if(isset($rawvalues)){
				if(empty($Element['_isMultiple'][$varkey])){
					$processedvalues = implode('', $rawvalues);
				}else{
					$processedvalues = $rawvalues;
				}
				$returnatts[$variable] = $processedvalues;
			}else{
				if($atts === 'export'){
					$returnatts[$variable] = null;
				}
			}
		}
		//dump($returnatts);
		// MAYBE do a match pairs here to balance the loop groups.
	return $returnatts;
	}
	return array();
}

