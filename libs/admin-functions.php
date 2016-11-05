<?php

/*
 * Brickroad Admin function library
 * (C) 2014 - David Cramer
 */


function brickroad_admin_processes(){
		// Process an Import
	if(!empty($_FILES['import']) && isset($_POST['_wpnonce'])){
		if(wp_verify_nonce($_POST['_wpnonce'],'cs-import-shortcode')){
			if(empty($_FILES['import']['error'])){
				$cat = 'nope-sorry';
				$loc = wp_upload_dir();

				if(move_uploaded_file($_FILES['import']['tmp_name'], $loc['path'].'/imported.msc')){
					$cat = brickroad_importScript($loc['path'].'/imported.msc');
				}
				wp_safe_redirect('?page=brickroad-admin&cat='.$cat);
				exit;
			}
		}
		wp_safe_redirect('?page=brickroad-admin');
		exit;
	}

		// Process an Export Element
	if(!empty($_POST['data']['_pluginName'])){

		if(wp_verify_nonce($_POST['_wpnonce'],'mspro-exoport-set')){
			//$proceedToExport = false;
			//foreach($_POST['data'] as $check=>$true){
				//if(strpos($check, '_toExport') !== false){
			$proceedToExport = true;
				//}
			//}
			if($proceedToExport == true){
				brickroad_exportPlugin($_POST['data'], $_POST['exportType']);
				//die;
			}else{                
				wp_redirect('admin.php?page=brickroad-admin&exporterror='.$_POST['data']['_pluginSet'].'&cat='.$_POST['data']['_pluginSet']);
				die;
			}
		}
	}

		// Process a Save Element
	if (!empty($_POST['data'])) {
		if(!wp_verify_nonce($_POST['_wpnonce'],'cs-edit-shortcode')){
			return;
		}
		$ID = brickroad_saveElement(stripslashes_deep($_POST['data']));
		$tabid = sanitize_key(strtolower($_POST['data']['_category']));
		wp_safe_redirect('?page=brickroad-admin&cat='.$tabid.'&el='.$ID);
		exit;
	}

		// Process general Admin Actions within CE admin
	if(!empty($_GET['action']) && !empty($_GET['element'])){
		
			// Process Activation of Element
		if($_GET['action'] == 'activate'){
			
			// base plugin
			$plugin = 'brickroad-elements/plugincore.php';
			// list all active
			$elements = brickroad_get_element('BR_ELEMENTS');
			$export = array();
			$currentExport = array(
				'_pluginName' => 'Brickroad Elements',
				'_pluginURI' => 'http://cramer.co.za/',
				'_pluginDescription' => 'Container plugin which houses all activated elements.',
				'_pluginAuthor' => 'David Cramer',
				'_pluginAuthorEmail' => 'david@digilab.co.za',
				'_pluginVersion' => BRICKROAD_VER,
				'_pluginAuthorURI' => 'http://cramer.co.za/',
				);

			deactivate_plugins( $plugin, false, is_network_admin() );
			
			$deactivate = false;
			if(isset($elements[$_GET['element']])){
				if(!empty($elements[$_GET['element']]['state'])){
					$deactivate = true;
				}
			}
			if(true === $deactivate){
				foreach($elements as $eid=>$settings){
					if(!empty($settings['state'])){
						if(isset($elements[$_GET['element']])){
							if($eid == $_GET['element']){
								continue;
							}
						}
						$export[] = $eid;
					}
				}
				if(!empty($export)){
					$currentExport['export'] = $export;
					brickroad_exportPluginPro($currentExport);			
					$result = activate_plugin($plugin, self_admin_url('admin.php?page=brickroad-admin&error=true&plugin=' . $plugin), is_network_admin() );
					if ( is_wp_error( $result ) ) {
						if ( 'unexpected_output' == $result->get_error_code() ) {
							// reactivate old set
							$redirect = self_admin_url('plugins.php?error=true&charsout=' . strlen($result->get_error_data()) . '&plugin=' . $plugin . "&plugin_status=$status&paged=$page&s=$s");
							wp_redirect(add_query_arg('_error_nonce', wp_create_nonce('plugin-activation-error_' . $plugin), $redirect));
							exit;
						} else {
							wp_die($result);
						}
					}
				}
				if(isset($elements[$_GET['element']])){
					$elements[$_GET['element']]['state'] = 0;
					unset($elements[$_GET['element']]['active_checksum']);
				}
			}else{
				foreach($elements as $eid=>$settings){
					if(!empty($settings['state'])){
						$export[] = $eid;						
					}
				}
				$currentExport['export'] = $export;
				$toExport = $currentExport;
				$toExport['export'][] = $_GET['element'];				
				brickroad_exportPluginPro($toExport);
				$result = activate_plugin($plugin, self_admin_url('admin.php?page=brickroad-admin&error=true&plugin=' . $plugin), is_network_admin() );
				if ( is_wp_error( $result ) ) {
					if ( 'unexpected_output' == $result->get_error_code() ) {
						// reactivate old set
						brickroad_exportPluginPro($currentExport);
						$result = activate_plugin($plugin, self_admin_url('admin.php?page=brickroad-admin&error=true&plugin=' . $plugin), is_network_admin() );				

						$redirect = self_admin_url('plugins.php?error=true&charsout=' . strlen($result->get_error_data()) . '&plugin=' . $plugin . "&plugin_status=$status&paged=$page&s=$s");
						wp_redirect(add_query_arg('_error_nonce', wp_create_nonce('plugin-activation-error_' . $plugin), $redirect));
						exit;
					} else {
						wp_die($result);
					}
				}
				if(isset($elements[$_GET['element']])){
					$elements[$_GET['element']]['active_checksum'] = md5(serialize(get_option($_GET['element'])));
					$elements[$_GET['element']]['state'] = 1;
				}
			}
			// update states			
			update_option('BR_ELEMENTS', $elements);
			$toCat = '__allactive____';
			if(isset($elements[$_GET['element']])){
				$toCat = sanitize_key($elements[$_GET['element']]['category']);
			}
			if(!empty($_GET['from'])){
				$toCat = '__allactive____';
			}
			if(isset($_GET['rebuild'])){
				if(isset($elements[$_GET['element']])){
					wp_redirect('admin.php?page=brickroad-admin&action=activate&element='.$_GET['element']);
				}else{
					wp_redirect('admin.php?page=brickroad-admin&action=activate');
				}
				die;
			}
			wp_redirect('admin.php?page=brickroad-admin&cat='.$toCat);
			die;
		}

			// Process create duplicate
		if($_GET['action'] == 'dup'){
			$Element= brickroad_get_element($_GET['element']);
			$Element['_ID'] = strtoupper(uniqid('EL'));
			$Element['_shortcode'] = $Element['_ID'];
			$Element['_name'] = $Element['_name'].' duplicate';
			$ID = brickroad_saveElement($Element);
			$tabid = sanitize_key(strtolower($Element['_category']));
			wp_safe_redirect('?page=brickroad-admin&cat='.$tabid.'&el='.$ID);                
			die;
		}
	}
		// End Process in Admin
}

function brickroad_docpage(){

	$data = json_decode(file_get_contents('http://docs.brickroad.co.za/api/'.$_GET['page']), true);
	if(empty($data)){
		echo 'Error loading documentation. You can view the online version here: <a href="http://docs.brickroad.co.za" target="_blank">docs.brickroad.co.za</a>';
		die;
	}

	$data['content'] = str_replace('href="', 'href="#" class="trigger" data-for="#trigger', $data['content']);
	echo str_replace('static/', 'http://docs.brickroad.co.za/static/', str_replace('h1>', 'h2>' , $data['content']));
	die;
}
function brickroad_docsloader(){

	$data = json_decode(@file_get_contents('http://docs.brickroad.co.za/api/index'), true);
	if(empty($data)){
		echo '<li><p style="padding:4px;">Error loading documentation. You can view the online version here:</p><li>';
		echo '<li><a href="http://docs.brickroad.co.za" target="_blank">docs.brickroad.co.za</a></li>';
		die;
	}
	// baldrick link
	$data['content'] = str_replace('href="', 'href="#" class="trigger" data-for="#trigger', $data['content']);
	$ajaxURL = admin_url('admin-ajax.php');
	foreach($data['menu'] as $menu){
		foreach($menu as $item){
			$auto = '';
			if($item['file'] == 'installation'){
				continue;
			}
			if($item['file'] == 'interface-admin'){
				$auto = 'data-autoload="true"';
			}
			echo '<li id="trigger'.$item['file'].'" class="trigger" '.$auto.' data-action="docpage" data-page="'.$item['file'].'" data-target="#docs-panel" data-request="'.$ajaxURL.'" data-active-class="current">';
			echo '<a title="'.sanitize_title(str_replace('__', '<strong>', str_replace('____', '</strong>', ucwords($item['title'])))).'" href="#docs-panel">'.str_replace('__', '<strong>', str_replace('____', '</strong>', ucwords($item['title']))).'</a>';
			echo '</li>';

		}

	}
	die;

}

function brickroad_menus() {

	global $brickroadAdminPage;
	add_menu_page("Brickroad - Elements Manager", "Brickroad", 'activate_plugins', "brickroad-admin", "brickroad_adminPage", 'dashicons-text');
	$brickroadAdminPage = add_submenu_page("brickroad-admin", 'Brickroad', 'Elements Manager', 'activate_plugins', "brickroad-admin", 'brickroad_adminPage', 'dashicons-editor-code');
	//$brickroadAdminNew = add_submenu_page("brickroad-admin", 'Brickroad', 'New Element', 'activate_plugins', "brickroad-admin&action=edit", 'brickroad_adminPage', BRICKROAD_URL."images/blank.png");
}

function brickroad_dismisssavepointer(){

	update_option('CE_DISMISS_SAVE', 1);
	die;
}
function brickroad_setPreviewBGColor(){

	update_option('CE_PREVIEWBGCOLOR', $_POST['color']);
	die;
}
function brickroad_setPreviewColor(){

	update_option('CE_PREVIEWCOLOR', $_POST['color']);
	die;
}

function brickroad_adminPage() {
	$settings = brickroad_get_element('CE_SETTINGS');
	
	// capture and update intro close
	if(!empty($_GET['intro'])){
		if($_GET['intro'] == 'show'){
			$showIntro = true;
		}
	}
	/*if(empty($settings['activationID'])){
		
		//$request_string = $this->prepare_request( 'update_check', $request_args );
		//$raw_response = wp_remote_post( $this->api_url, $request_string );
		//echo 'asd';
		include BRICKROAD_PATH . 'activate.php';
		return;
	}*/
	
	// Load Intro!
	if(empty($settings['intro_shown']) || !empty($showIntro)){

		$settings['intro_shown'] = 'yes';
		update_option('CE_SETTINGS', $settings);
		
		wp_enqueue_script('unslide', BRICKROAD_URL.'libs/js/unslider.min.js',array('jquery'), true);
		
		include BRICKROAD_PATH . 'intro.php';
		return;
	}


	if (!empty($_GET['action'])) {
		switch ($_GET['action']) {
			case 'settings':
			include BRICKROAD_PATH . 'settings.php';
			break;
			case 'edit':
			include BRICKROAD_PATH . 'editor.php';
			break;
			default:
			include BRICKROAD_PATH . 'admin.php';
			break;
		}
	} else {
		include BRICKROAD_PATH . 'admin.php';
	}
}

function brickroad_configOption($ID, $Name, $Type, $Title, $Config, $caption = false, $inputTags = '') {

	$Return = '';

	switch ($Type) {
		case 'hidden':
		$Val = '';
		if (!empty($Config['_' . $Name])) {
			$Val = $Config['_' . $Name];
		}
		$Return .= '<input type="hidden" name="data[_' . $Name . ']" id="' . $ID . '" value="' . $Val . '" />';
		break;
		case 'dropdown':
		$Val = '';
		if (!empty($Config['_' . $Name])) {
			$Val = $Config['_' . $Name];
		}
		$Return .= '<label>'.$Title . '</label> ';
		$Return .= '<select name="data[_' . $Name . ']" id="' . $ID . '">';

		foreach($inputTags as $key=>$label){
			$sel = '';
			if($Val == $key){
				$sel = 'selected="selected"';
			}
			$Return .= "<option value='".$key."' ".$sel.">".$label."</option>";
		}

		$Return .= '</select>';
		break;
		case 'textfield':
		$Val = '';
		if (!empty($Config['_' . $Name])) {
			$Val = $Config['_' . $Name];
		}
		$Return .= '<label>'.$Title . '</label> <input type="text" name="data[_' . $Name . ']" id="' . $ID . '" value="' . htmlentities($Val) . '" '.$inputTags.' />';
		break;
		case 'textarea':
		$Val = '';
		if (!empty($Config['_' . $Name])) {
			$Val = $Config['_' . $Name];
		}
		$Return .= '<label>'.$Title . '</label> <textarea name="data[_' . $Name . ']" id="' . $ID . '" cols="70" rows="25">' . htmlentities($Val) . '</textarea>';
		break;
		case 'radio':
		$parts = explode('|', $Title);
		$options = explode(',', $parts[1]);
		$Return .= '<label class="multiLable">'.$parts[0]. '</label>';
		$index = 1;
		foreach ($options as $option) {
			$sel = '';
			if (!empty($Config['_' . $Name])) {
				if ($Config['_' . $Name] == $index) {
					$sel = 'checked="checked"';
				}
			}else{
				if(strpos($option, '*') !== false){
					$sel = 'checked="checked"';
				}

			}
			if (empty($Config)) {
				if ($index === 1) {
					$sel = 'checked="checked"';
				}
			}
			$option = str_replace('*', '', $option);
			$Return .= '<div class="toggleConfigOption"> <input type="radio" name="data[_' . $Name . ']" id="' . $ID . '_' . $index . '" value="' . $index . '" ' . $sel . '/> <label for="' . $ID . '_' . $index . '" style="width:auto;">' . $option . '</label></div>';
			$index++;
		}
		break;
		case 'checkbox':
		$sel = '';
		if (!empty($Config['_' . $Name])) {
			$sel = 'checked="checked"';
		}

		$Return .= '<input type="checkbox" name="data[_' . $Name . ']" id="' . $ID . '" value="1" '.$sel.' /><label for="' . $ID . '" style="margin-left: 10px; width: 570px;">'.$Title.'</label> ';
		break;
	}
	$captionLine = '';
	if(!empty($caption)){
		$captionLine = '<div class="brickroad_captionLine description">'.$caption.'</div>';
	}
	return '<div class="brickroad_configOption '.$Type.'" id="config_'.$ID.'">' . $Return . $captionLine.'</div>';
}
/*
function brickroad_rebuild_emplates(){
	$elements = brickroad_get_element('BR_ELEMENTS');
	$TemplateElements = array();
	foreach($elements as $eid=>$element){
		
		if($element['elementType'] == '6' && $element['state'] == '1'){
			$Data = brickroad_get_element($eid);
			$TemplateElements[$Data['_for_type']][$Data['_template_type']][$Data['_template_depth']][$Data['_ID']] = array(
				'id' => $Data['_ID'],
				'slug' => $Data['_shortcode']
			);
		}
	}
	update_option('CE_TEMPLATES', $TemplateElements);


}
*/

function brickroad_saveElement($Data, $savetype = 'full') {
	global $wp_version, $wp_filesystem;

	// okay, let's see about getting 
	$url = wp_nonce_url('admin.php?page=brickroad-admin','brickroad-admin-element');
	$creds = request_filesystem_credentials( $url );
	// now we have some credentials, try to get the wp_filesystem running
	if ( ! WP_Filesystem($creds) ) {
		// our credentials were no good, ask the user for them again
		request_filesystem_credentials( $url );
		return true;
	}

	delete_option('_brickroad_js_cache');
	delete_option('_brickroad_css_cache');


	if (empty($Data['_ID'])) {
		$Data['_ID'] = strtoupper(uniqid('EL'));
	}
	if (empty($Data['_name'])) {
		$Data['_name'] = $Data['_ID'];
	}
	if (empty($Data['_category'])) {
		$Data['_category'] = 'ungrouped';
		$_POST['data']['_category'] = 'ungrouped';
	}else{
		$Data['_category'] = strtolower($Data['_category']);
	}
	$groupOrder = array();

	if(!empty($Data['_groupkeys']) && !empty($Data['_variable'])){
		foreach($Data['_groupkeys'] as &$keystring){
			$ordering = explode(',',$keystring);
			foreach($ordering as &$gkey){
				$groupOrder[$gkey] = $Data['_variable'][$gkey];
			}
		}
		$Data['_variable'] = $groupOrder+$Data['_variable'];
	}
	$elements = brickroad_get_element('BR_ELEMENTS');
	$AlwaysLoads = brickroad_get_element('CE_ALWAYSLOAD');	
	$TemplateElements = brickroad_get_element('CE_TEMPLATES');
	
	if($Data['_elementType'] == 4){
		$AlwaysLoads[$Data['_ID']]['alwaysLoad'] = $Data['_alwaysLoadPlacement'];
		//update_option('CE_ALWAYSLOAD', $AlwaysLoads);
	}else{
		if(!empty($AlwaysLoads[$Data['_ID']]['alwaysLoad'])){
			unset($AlwaysLoads[$Data['_ID']]);
			//update_option('CE_ALWAYSLOAD', $AlwaysLoads);
		}
	}

	$elements[$Data['_ID']]['name'] = $Data['_name'];
	$elements[$Data['_ID']]['description'] = $Data['_description'];
	if(!empty($Data['_posttype'])){
		// Preflush!
		flush_rewrite_rules();
		$elements[$Data['_ID']]['menuicon'] = $Data['_menuicon'];
		$elements[$Data['_ID']]['posttype'] = 1;
		$elements[$Data['_ID']]['singleName'] = $Data['_singleName'];
		$elements[$Data['_ID']]['pluralName'] = $Data['_pluralName'];
		$elements[$Data['_ID']]['browsable'] = 0;
		if(!empty($Data['_browsable'])){
			$elements[$Data['_ID']]['browsable'] = $Data['_browsable'];
			//die;
		}
		if(!empty($Data['_template'])){
			$elements[$Data['_ID']]['template'] = $Data['_template'];
			//$elements[$Data['_ID']]['templateElement'] = $Data['_templateElement'];
			//die;
		}
		// Pickup Taxonomies
		$taxonomies = array();
		foreach($Data as $varline=>&$varval){
			if(substr($varline, 0, 5) === '_tax_'){
				$taxonomies[] = substr($varline, 5);
			}
		}
		if(!empty($taxonomies)){
			$elements[$Data['_ID']]['taxonomies'] = $taxonomies;
		}else{
			unset($elements[$Data['_ID']]['taxonomies']);
		}
		flush_rewrite_rules();
	}else{
		$elements[$Data['_ID']]['posttype'] = 0;
	}
	$elements[$Data['_ID']]['category'] = $Data['_category'];
	if(!empty($Data['_removelinebreaks'])){
		$elements[$Data['_ID']]['removelinebreaks'] = 1;
	}else{
		$elements[$Data['_ID']]['removelinebreaks'] = 0;
	}
	if (!empty($Data['_shortcode'])) {
		$Data['_shortcode'] = sanitize_key($Data['_shortcode']);
		$elements[$Data['_ID']]['shortcode'] = $Data['_shortcode'];
	}else{
		$Data['_shortcode'] = sanitize_key($Data['_name']);
		$elements[$Data['_ID']]['shortcode'] = $Data['_shortcode'];
	}
	$elements[$Data['_ID']]['codeType'] = $Data['_shortcodeType'];
	$elements[$Data['_ID']]['elementType'] = $Data['_elementType'];
	if(!isset($elements[$Data['_ID']]['state'])){
		$elements[$Data['_ID']]['state'] = 0;
	}
	if (!empty($Data['_variable'])) {
		foreach ($Data['_variable'] as $Key => $Varible) {
			$Data['_variable'][$Key] = sanitize_key($Varible);
			if(empty($Data['_tabgroup'][$Key])){
				$Data['_tabgroup'][$Key] = 'General Settings';
			}
		}
		$elements[$Data['_ID']]['variables']['names'] = $Data['_variable'];
		$elements[$Data['_ID']]['variables']['defaults'] = $Data['_variableDefault'];
		$elements[$Data['_ID']]['variables']['info'] = $Data['_variableInfo'];
		$elements[$Data['_ID']]['variables']['type'] = $Data['_type'];
		if(!empty($Data['_isMultiple'])){
			$elements[$Data['_ID']]['variables']['multiple'] = $Data['_isMultiple'];
		}
	} else {
		unset($elements[$Data['_ID']]['variables']);
	}

	// save current checksum
	$elements[$Data['_ID']]['current_checksum'] = md5(serialize($Data));

	update_option($Data['_ID'], $Data);
	update_option('BR_ELEMENTS', $elements);

	if($savetype == 'full'){

		$plugin_path = BRICKROAD_PATH . 'elements/' . sanitize_title( $Data['_ID'] );
		// kill off the preview to not keep it around.
		if ( file_exists( $plugin_path ) ) {
			$wp_filesystem->delete( $plugin_path, true );
		}

	}
	////brickroad_rebuild_emplates();
	// rebuild active elements
	if(!empty($elements[$Data['_ID']]['state'])){
		// reactivate element
		//admin.php?page=brickroad-admin&action=activate&element=EL52D25D0D51CEC
	}

	return $Data['_ID'];
}


function brickroad_deleteElement() {

	$EID = $_POST['EID'];

	$Elements = brickroad_get_element('BR_ELEMENTS');
	delete_option($EID);
	unset($Elements[$EID]);
	update_option('BR_ELEMENTS', $Elements);
	echo true;
	die();
}
function brickroad_applyElement() {

	parse_str(stripslashes($_POST['formData']), $Data);
	if(get_magic_quotes_gpc()){
		$Data = array_map('stripslashes_deep',$Data);
	}
	
	if(!empty($_POST['saveatts'])){
		if(!empty($Data[$Data['data']['_shortcode']])){
			set_transient( "_".$Data['data']['_shortcode']."_preview", $Data[$Data['data']['_shortcode']], 86400);
		}
	}
	if(empty($_POST['preview'])){
		brickroad_saveElement($Data['data'], 'preview');
	}
	$Data = $Data['data'];
	$preview_element = array(
		"_pluginName"			=> $Data['_ID'],
		"_pluginURI"			=> 'http://cramer.co.za/',
		"_pluginDescription"	=> 'Preview Render of element',
		"_pluginAuthor"			=> '',
		"_pluginAuthorEmail"	=> 'david@digilab.co.za',
		"_pluginVersion"		=> BRICKROAD_VER,
		"_pluginAuthorURI"		=> 'http://cramer.co.za/',
		"export"				=> array(
			$Data['_ID']
		)
	);
	brickroad_exportPluginPro($preview_element, 'preview');	
	
	include 'editorpreview.php';


	die();
}
function brickroad_upgradeElements() {

	$Elements = brickroad_get_element('BR_ELEMENTS');
	foreach($Elements as $ID=>&$cfg){
		if(!isset($cfg['current_checksum'])){
			// do an upgrade
			$element = get_option($ID);
			if(empty($element)){
				// rouge kill it.
				unset($Elements[$ID]);
			}else{
				// catch the active hash
				if(isset($element['_variable'])){
					if(isset($element['_tabgroup'])){
						// atts groups upgrade
						if(!isset($element['_groupkeys'])){
							$keys = array();
							$groups = array();
							foreach($element['_tabgroup'] as $key=>$group){
								$keys[$group][] = $key;
								if(!isset($groups[$group])){
									$groups[$group] = $key;
								}
							}
							foreach($keys as $group=>$set){
								$element['_groupkeys'][] = implode(',',$set);
								$element['_groupvals'][] = $group;
							}
							// is multi upgrade
							foreach($element['_variable'] as $key=>$name){
								if(!isset($element['_isMultiple'][$key])){
									$element['_isMultiple'][$key] = null;
									$element['_group'][$key] = null;
								}else{
									$element['_group'][$key] = $groups[$element['_tabgroup'][$key]];
								}
							}
						}
					}
				}
				if(!isset($cfg['codeType'])){
					$cfg['codeType'] = 1;
				}
				if(!isset($cfg['elementType'])){
					$cfg['elementType'] = 1;
				}
				if(!isset($cfg['state'])){
					$cfg['state'] = 1;
				}
				if(!isset($cfg['shortcode'])){
					$cfg['shortcode'] = $ID;
				}

				$cfg['current_checksum'] = md5(serialize($element));
				update_option($ID, $element);
			}
			if(!empty($cfg['state'])){
				if(!isset($cfg['active_checksum'])){
					$cfg['active_checksum'] = md5(serialize($element));
				}
			}
			
		}
	}
	update_option('BR_ELEMENTS', $Elements);
}


function brickroad_ajax_javascript() {

	?>
	<script type="text/javascript">

		function brickroad_upgradeElements(){
			var data = {
				action: 'upgrade_elements'
			};
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('.elementUpgradeNodes').css('background', '#4e9700');
				jQuery('#upgradeElementsButton').parent().html('Elements Upgraded, Reloading page...');
				setTimeout(function(){
					window.location = "admin.php?page=brickroad-admin&action=activate&element=_all__&from=active";
				}, 500);
			});
		}
		function brickroad_deleteElement(eid){
			//if(confirm('Are you sure?')){
				var data = {
					action: 'delete_element',
					EID: eid
				};
				jQuery.post(ajaxurl, data, function(response) {
					if(response == 1){
						jQuery('.element_'+eid).slideUp('fast', function(){
							jQuery('.element_'+eid).remove();
							var newval = parseFloat(jQuery('.current .cs-elementCount').html()-1);
							if(newval > 0){
								jQuery('#mainNav .current .cs-elementCount').html(newval);
							}else{
								jQuery('#mainNav .current').slideUp();
							}
						});
					}
				});
			//}else{
			//    jQuery('.buttons_'+eid).slideToggle();
			//}
		}
		function brickroad_applyElement(eid){

			jQuery('#saveIndicator').slideDown(100);
			var data = {
				action: 'apply_element',
				EID: eid,
				formData: jQuery('#elementEditForm').serialize()
			};

			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#ID').val(response);
				jQuery('#header .title h2').html('Editing: '+jQuery('#name').val());
				jQuery('#saveIndicator').slideUp(100);
			});
			//brickroad_rebuild_emplates();

		}
		function brickroad_moveElement(eid, cat){
			var data = {
				action: 'move_element',
				EID: eid,
				cat: cat
			};
			jQuery.post(ajaxurl, data, function(response) {
			});
		}
		function brickroad_setToolTips(state){

			var data = {
				action: 'set_tooltips',
				state: state
			};
			jQuery.post(ajaxurl, data, function(response) {
			});
		}
	</script>
	<?php
}
function brickroad_setTooltips(){

	$settings = brickroad_get_element('CE_SETTINGS');
	if(empty($settings)){
		$settings['disableTooltips'] = 0;
	}
//        vardump($_POST);
	if(!empty($_POST['state'])){
		$settings['disableTooltips'] = 1;
	}else{
		$settings['disableTooltips'] = 0;
	}
	update_option('CE_SETTINGS', $settings);
	die;

}
function brickroad_detectRogues(){
	global $wpdb;

	if ( !is_plugin_active( 'brickroad-elements/plugincore.php' ) ) {
		return true;
		$Elements = brickroad_get_element('BR_ELEMENTS');
		if(!empty($Elements)){
			foreach($Elements as $ID=>&$cfg){
				if($cfg['state'] == '1'){
					$cfg['state'] = 0;
				}
			}
				//update_option('BR_ELEMENTS', $Elements);
		}
	}


		/*
		$Elements = brickroad_get_element('BR_ELEMENTS');
		if(!empty($Elements)){
			foreach($Elements as $ID=>&$cfg){
				$elementsFound[] = "'".$ID."'";
			}
		}        
		$excludes = '';
		if(!empty($elementsFound)){
			$excludes = "AND `option_name` NOT IN (".implode(',',$elementsFound).")";
		}
		$rogue = $wpdb->get_results("SELECT `option_name` FROM `".$wpdb->options."` WHERE `option_name` LIKE 'EL%' AND LENGTH(`option_name`) = 15 ".$excludes.";", ARRAY_A);

		if(!empty($rogue)){
			foreach($rogue as $ElementOption){
				$Element = brickroad_get_element($ElementOption['option_name']);
				brickroad_saveElement($Element);
			}
		}*/
	}
	
	function brickroad_moveElement(){

		$elements = brickroad_get_element('BR_ELEMENTS');
		$EID = str_replace('element_', '', $_POST['EID']);
		$element = brickroad_get_element($EID);
		$cat = $_POST['cat'];
		$elements[$EID]['category'] = $cat;
		$element['_category'] = $cat;
		update_option($EID, $element);
		update_option('BR_ELEMENTS', $elements);
		echo $_POST['EID'];
		die;
	}

	function brickroad_importScript($file){
	//$data = unserialize(base64_decode(file_get_contents($file)));
		$data = json_decode(file_get_contents($file), true);
		if(empty($data)){
			$data = unserialize(base64_decode(file_get_contents($file)));
		}

		if(!empty($data['assets'])){
			foreach($data['assets'] as $element=>$assetList){
				foreach($assetList as $assetKey=>$assetData){
					
					$uploads = wp_upload_dir();					
					//dump($data['exportCfg'][$element]['_assetLabel'][$assetKey]);
					//dump(gzuncompress(base64_decode($assetData)));
					$pathinfo = wp_upload_bits($assetData['filename'], null, gzuncompress(base64_decode($assetData['data'])));
					$mime_type = wp_check_filetype($pathinfo['file']);
					
					//create an array of attachment data to insert into wp_posts table
					$asset = array(
						'post_author' => get_current_user_id(), 
						'post_date' => current_time('mysql'),
						'post_date_gmt' => current_time('mysql'),
						'post_title' => $assetData['filename'], 
						'post_status' => 'inherit',
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_name' => sanitize_title_with_dashes(str_replace("_", "-", $assetData['filename'])),
						'post_modified' => current_time('mysql'),
						'post_modified_gmt' => current_time('mysql'),
						'post_type' => 'attachment',
						'guid' => $pathinfo['url'],
						'post_mime_type' => $mime_type['type'],
						'post_excerpt' => '',
						'post_content' => ''
						);
					//insert the database record
					$asset_id = wp_insert_post( $asset );
					add_post_meta($asset_id, '_wp_attached_file', ltrim($uploads['subdir'].'/'.basename($pathinfo['file']), '/'));

					$data['exportCfg'][$element]['_assetURL'][$assetKey] = $asset_id;
				}
			}
		}
	//dump(file_get_contents($file));
	//die;
		$elements = brickroad_get_element('BR_ELEMENTS');
		if(empty($data['exportPack'])){
			unlink($file);
			return 'error: pack was empty. Perhaps the export didn\'t have any elements selected?';
		}
		if(is_array($elements)){
			$elements = array_merge($elements, $data['exportPack']);
		}else{
			$elements = $data['exportPack'];
		}
		update_option('BR_ELEMENTS' ,$elements);
		foreach($data['exportCfg'] as $id=>$cfg){
			update_option($id ,$cfg);
		}
		unlink($file);
		return $data['exportSettings']['_pluginSet'];
	}

//* Admin Header Scripts
	function brickroad_enqueue_scripts_styles($hook){
		global $post;

	// Global Styles
		wp_enqueue_style('brickroad_icons', BRICKROAD_URL . 'styles/mscpicons.css');


		if($hook === 'toplevel_page_brickroad-admin'){

			wp_enqueue_script("jquery");
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('jquery-ui-draggable');
			wp_enqueue_script('jquery-ui-droppable');

			wp_enqueue_script('baldrick', BRICKROAD_URL . 'libs/js/baldrick.js', false, false, true);

		// ADMIN MENU & EDITOR STYLES and SCRIPTS
			wp_enqueue_style('wp-pointer');
			wp_enqueue_script('wp-pointer');

			wp_enqueue_style('font-awesome', BRICKROAD_URL.'styles/font-awesome.min.css');
			wp_enqueue_style('brickroad-panels', BRICKROAD_URL.'styles/panel.css');
			wp_enqueue_style('brickroad-core', BRICKROAD_URL.'styles/core.css');

			wp_enqueue_script('bootstrap-tooltips', BRICKROAD_URL . 'libs/js/tooltips.js', false);
			wp_enqueue_style('bootstrap-tooltips', BRICKROAD_URL . 'styles/tooltips.css', false);



			/*
			wp_enqueue_script('codemirror', BRICKROAD_URL . 'codemirror/lib/codemirror.js', false);
			wp_enqueue_script('codemirror-runmode', BRICKROAD_URL . 'codemirror/lib/util/runmode.js', false);
			wp_enqueue_script('codemirror-mode-php', BRICKROAD_URL . 'codemirror/mode/php/php.js', false);
			wp_enqueue_script('codemirror-mode-clike', BRICKROAD_URL . 'codemirror/mode/clike/clike.js', false);
			*/

			if(!empty($_GET['action'])){
				add_action('admin_footer-'.$hook, 'brickroad_media_modals');

				wp_enqueue_script('modernize', BRICKROAD_URL . 'libs/js/modernize.js', false);

				wp_enqueue_style('brickroad_adminbuttons', BRICKROAD_URL . 'styles/buttons.css');

				wp_enqueue_style('codemirror-simple-hint', BRICKROAD_URL . 'codemirror/lib/util/simple-hint.css', false);
				wp_enqueue_style('codemirror-dialog-css', BRICKROAD_URL . 'codemirror/lib/util/dialog.css', false);
				wp_enqueue_style('bootstrap-typeahead', BRICKROAD_URL . 'styles/dropdown.css', false);
				
				wp_enqueue_style('codemirror', BRICKROAD_URL . 'codemirror/lib/codemirror.css', false);
				wp_enqueue_script('codemirror', BRICKROAD_URL . 'codemirror/codemirror-compressed.js', false);


				/*
				wp_enqueue_script('codemirror-overlay', BRICKROAD_URL . 'codemirror/lib/util/overlay.js', false);
				wp_enqueue_script('codemirror-closetag', BRICKROAD_URL . 'codemirror/lib/util/closetag.js', false);
				wp_enqueue_script('codemirror-mode-css', BRICKROAD_URL . 'codemirror/mode/css/css.js', false);
				wp_enqueue_script('codemirror-mode-js', BRICKROAD_URL . 'codemirror/mode/javascript/javascript.js', false);
				wp_enqueue_script('codemirror-mode-xml', BRICKROAD_URL . 'codemirror/mode/xml/xml.js', false);
				wp_enqueue_script('codemirror-mode-htmlmixed', BRICKROAD_URL . 'codemirror/mode/htmlmixed/htmlmixed.js', false);
				wp_enqueue_script('codemirror-simple-hint-js', BRICKROAD_URL . 'codemirror/lib/util/simple-hint.js', false);
				wp_enqueue_script('codemirror-js-hint', BRICKROAD_URL . 'codemirror/lib/util/javascript-hint.js', false);
				wp_enqueue_script('codemirror-dialog-js', BRICKROAD_URL . 'codemirror/lib/util/dialog.js', false);
				wp_enqueue_script('codemirror-searchcursor-js', BRICKROAD_URL . 'codemirror/lib/util/searchcursor.js', false);
				wp_enqueue_script('codemirror-multiplex-js', BRICKROAD_URL . 'codemirror/lib/util/multiplex.js', false);
				wp_enqueue_script('codemirror-search-js', BRICKROAD_URL . 'codemirror/lib/util/search.js', false);
				//codemirror-compressed.js
				*/


				wp_enqueue_script('bootstrap-typeahead', BRICKROAD_URL . 'libs/js/typeahead.js', false);        
				wp_enqueue_script('general-editor', BRICKROAD_URL . 'libs/js/edit.js', false, false, true);       

			// Final editor styling
				wp_enqueue_style('brickroad_editor', BRICKROAD_URL . 'editorcss/editor.css');

				wp_enqueue_media();
				wp_enqueue_script('media-upload');

			}

		}
	// Hooks for alwaysload and code
	//settings_page_el51ff46a732283
		$elements = brickroad_get_element('BR_ELEMENTS');
		$hooksArray  = array('post-new.php', 'post.php', 'edit.php', 'widgets.php');
		foreach($elements as $id=>&$element){
			if($element['state'] === 1 && ($element['elementType'] == 5 || $element['elementType'] == 4)){
				$hooksArray[] = 'settings_page_'.strtolower($id);
				wp_enqueue_style('brickroad-core', BRICKROAD_URL.'styles/core.css');
			}
			if(!empty($element['variables']['type'])){
				if(in_array('Color Picker', $element['variables']['type'])){
					$hasColor = true;
				}
			}

		}


	}


	function brickroad_exportPlugin($data, $type){
		global $wp_version;

		if($type == 'script'){

			$elements = get_option('BR_ELEMENTS');
			$forExport = array();
			$Shortcodes = array();
			$widgetsToInclude = array();
			$WidgetActions = array();
			$settingsAjax = array();
			$exportConfigs = array();
			$newPlugin_path = WP_PLUGIN_DIR.'/'.sanitize_file_name(strtolower($data['_pluginName']));
			$uploadVars = wp_upload_dir();
			$assetsToCopy = array();
			$Widgets = array();
			$Posttypes = array();
			$alwaysLoads= array();
			$pluginAlwaysLoadIncludes = array();
			$slugsAvailable = array();
			$pluginFunctionsInclude = '';
			$hasColorPicker = array();		
			
			$outData = array();
			$outData['exportSettings'] = $data;
			foreach($elements as $id=>$element){               
				if(in_array($id, $data['export'])){
					$outData['exportPack'][$id] = $element;
					$element = get_option($id);                    
					if(!empty($element['_assetURL'])){
						if(wp_mkdir_p(BRICKROAD_PATH.'assets/'.$data['_pluginSet'])){
							foreach($element['_assetURL'] as $assetKey=>$assetItem){
								if(!empty($assetItem)){
									$assetPath = get_attached_file($assetItem);
									if(file_exists($assetPath)){
										$element['_assetURL'][$assetKey] = basename($assetPath);
										$outData['assets'][$id][$assetKey]['filename'] = basename($assetPath);
										$outData['assets'][$id][$assetKey]['data'] = base64_encode(gzcompress(file_get_contents($assetPath),9));										
									}else{
										//dump($element);
									}
								}
							}
						}
					}
					$outData['exportCfg'][$id] = $element;
				}
			}
			$outData = json_encode($outData);
			header('Content-Length: '.strlen($outData));
			header('Content-Disposition: attachment; filename="'.str_replace('__', '', $data['_pluginSet']).'.json"');
			header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
			header('Pragma: no-cache');
			echo $outData;
			die;

		}
		
		if(function_exists('brickroad_exportPluginPro')){
			update_option('_msp_'.sanitize_key($data['_pluginSet']),$data);
			brickroad_exportPluginPro($data, $type);
		}

	}



function brickroad_media_modals(){
	?>
	<script type="text/javascript">
	function brickroad_open_imagemodal(el){
		var button = jQuery(el);
		var preview = button.parent();
		var frame = wp.media({
			title : 'Select Image',
			multiple : false,
			library: { type: 'image'},
			button : { text : 'Use Image' }
		});
				// Runs on select
				frame.on('select',function(){
					var objSettings = frame.state().get('selection').first().toJSON(),
						id = jQuery('#'+button.parent().parent().data('field')+'_id');

					preview.html('<span class="remove">&times;</span>');
					jQuery.each(objSettings.sizes, function(size,opts){
						var issel = '';
						preview.append('<span data-size="'+opts.width+'x'+opts.height+'" class="sizes '+size+'">'+size+'</span>');
					});
					if(typeof objSettings.sizes.medium !== 'undefined'){
						var srcurl = objSettings.sizes.medium.url,
							sizex = objSettings.sizes.medium.width,
							sizey = objSettings.sizes.medium.height;
							setsize = 'medium',
							size = objSettings.sizes.medium.width+'x'+objSettings.sizes.medium.height;
						
						preview.append('<span class="preview">'+size+'</span>');
						preview.find('.medium').addClass('selected');

					}else{
						var srcurl = objSettings.sizes.full.url,
							sizex = objSettings.sizes.full.width,
							sizey = objSettings.sizes.full.height,
							setsize = 'full',
							size = objSettings.sizes.full.width+'x'+objSettings.sizes.full.height;
						
						preview.append('<span class="preview">'+size+'</span>');
						preview.find('.full').addClass('selected');

					}
					id.val(objSettings.id+','+setsize);
					// will make ratio based later
					if(sizex > sizey){
						var response = '100% auto';
					}else if(sizex <= sizey){
						var response = 'auto 100%';
					}
					preview.css('background', '#efefef url('+srcurl+') center center / '+response+' no-repeat').append('<span class="filechanger-btn brickroad_media_select changer">Change Image</span>');

				});
				frame.open();
			}
			function brickroad_open_filemodal(el){
				var button = jQuery(el);
				var frame = wp.media({
					title : 'Select File',
					multiple : false,
					button : { text : 'Use File' }
				});
				var preview = button.parent();

				// Runs on select
				frame.on('select',function(){
					var objSettings = frame.state().get('selection').first().toJSON(),
						id = jQuery('#'+button.parent().parent().data('field')+'_id');
						//console.log(button.parent().parent());
					id.val(objSettings.id);
					//console.log(objSettings);
					var icon = '<img class="filepreview" src="'+objSettings.icon+'">';
					if(objSettings.type === 'image'){
						if(objSettings.sizes.thumbnail){
							icon = '<img class="filepreview image" src="'+objSettings.sizes.thumbnail.url+'">';
						}else if(objSettings.sizes.full){
							icon = '<img class="filepreview" src="'+objSettings.sizes.full.url+'">';
						}
					}
					preview.html(icon+objSettings.filename+' <span class="filechanger-btn brickroad_uploader button">Change File</span> <span class="button removefile">&times;</span>');

				});

				// Open ML
				frame.open();
			}
			</script>
			<?php
		}
		function brickroad_widget_javascript(){
			?>
			<script type="text/javascript">

			jQuery('#widgets-right').on('mouseenter', '.brickroad_widgetpanel .minicolorPicker,.brickroad_widgetpanel .miniColors-trigger-fake',function(){
				jQuery('.miniColors-trigger-fake').remove();
				jQuery('.minicolorPicker').miniColors();

			});


			if((typeof wp !== 'undefined')){
				var _custom_media = true,
				_orig_send_attachment = wp.media.editor.send.attachment;

				jQuery('#widgets-right').on('click','.brickroad_media_select', function() {
					brickroad_open_imagemodal(this);
				});

				jQuery('#widgets-right').on('click','.brickroad_widgetpanel .brickroad_uploader', function() {
					brickroad_open_filemodal(this);
				});
			}

			jQuery('#widgets-right').on('click','.brickroad_widgetpanel .removefile', function() {
				var box = jQuery(this).parent(),
					field = jQuery('#'+box.parent().data('field')+'_id').val('');
				////console.log(box);
				box.html('<span class="noselection brickroad_uploader button">Select File</span>').removeAttr('style');
			});
			jQuery('#widgets-right').on('click','.brickroad_widgetpanel .remove', function() {
				var box = jQuery(this).parent(),
					field = jQuery('#'+box.parent().data('field')+'_id').val('');
				////console.log(box);
				box.html('<span class="noselection brickroad_media_select button">Select Image</span>').removeAttr('style');
			});
			//'<span class="noselection brickroad_media_select button">Select Image</span>'

			jQuery('#widgets-right').on('click','.sizes', function() {
				var clicked = jQuery(this),
					field = jQuery('#'+clicked.parent().parent().data('field')+'_id');
				clicked.parent().find('.sizes').removeClass('selected');
				clicked.parent().find('.preview').html(clicked.data('size'));
				clicked.addClass('selected');
				if(field.length){
					field.val(field.val().split(',')[0]+','+clicked.html());
				}
			});			

			jQuery('#widgets-right').on('click','.brickroad_widgetpanel .removeRow', function(event){
				jQuery(this).parent().parent().remove();
			});

			jQuery('#widgets-right').on('click','.brickroad_widgetpanel .addRow', function(event){

				event.preventDefault();
				jQuery(this).before('<input type="hidden" value="'+jQuery(this).attr('ref')+'" name="'+jQuery(this).parent().attr('ref')+'" />');

			})

			jQuery('#widgets-right').on('change', ".brickroad_widgetpanel .msc-cat-select",function(){

				var id = this.id;
				var ref = jQuery(this).attr('ref');                
				jQuery('#ele'+id).html('<img alt="" title="" class="" src="<?php echo admin_url(); ?>images/wpspin_light.gif" style="visibility: visible;">');

				var data = {
					action: 'brickroad_selectedgetcat',
					cat: this.value,
					id:id,
					ele: ref
				};
		//jQuery('#mediaPanel').html('<div class="loading">Loading</div>');
		jQuery.post(ajaxurl, data, function(response) {
			jQuery('#ele'+id).html(response);
		});

	});
			jQuery('#widgets-right').on('change', ".msc-ele-select", function(){
				jQuery('#form_'+this.id).html('');
			});
			jQuery('#widgets-right').on('click',".show-elements-tab", function(event){
				event.preventDefault();
				jQuery(this).toggleClass('active');
				jQuery(jQuery(this).attr('href')).slideToggle();
			});
			function brickroad_addGroup(id, prefix){
				number = jQuery('.group'+id).length+1;
		//alert(jQuery('.group'+id).length);
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
		var data = {
			action: 'fileatt_addgroup',
			group: id,
			number: number,
			nameprefix: prefix
		};
		jQuery('#mediaPanel').html('<div class="loading">Loading</div>');
		jQuery.post(ajaxurl, data, function(response) {
			jQuery('#tool'+id).before(response);
		});
	}

	</script>
	<?php
}


