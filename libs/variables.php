<?php

//dump($Element,0);
// input types
global $types;
$types = array(
	'Text Field'			=> 'textfield',
	'Small Text Field'		=> 'smalltextfield',
	'Text Box'				=> 'textbox',
	'Dropdown'				=> 'dropdown',
	'Checkbox - inline'		=> 'checkbox-inline',
	'Checkbox'				=> 'checkbox',
	'Radio - inline'		=> 'radio-inline',
	'Radio'					=> 'radio',
	'Toggle Switches'		=> 'onoff',
	'Color Picker'			=> 'colorpicker',
	'Date'					=> 'date',
	'Slider'				=> 'slider',
	'File'					=> 'file',
	'Image'					=> 'image',
	'Post Type Selector'	=> 'posttypeselector',
	'Code Editor'			=> 'codeeditor',
	'Content Editor'		=> 'contenteditor',
	'Field Type Element'	=> 'fieldelement',
	//'Custom'			=> ''
);
ksort($types);
// delete group button template
function get_deleteGroup(){
	return '<div id="var-delete-group"><button class="button button-block" id="delete-group" type="button">Delete Group</button></div>';
}

// group line template
function get_groupLine($id, $label, $description="", $string=null, $active = '', $typeicon ='icon-folder-close-alt', $debug = 'hidden'){
	$out = '<li>';
		$out .= '<a href="#'.$id.'" id="groupline'.$id.'" class="grouptab '.$active.'">';
			$out .= '<i class="'.$typeicon.' grouptype"></i> <span class="grouplabel-text">'.$label.'</span>';
			$out .= '<i class="selectarrow icon-angle-right pull-right">&nbsp;</i>';
		$out .= '</a>';
		
		$out .= '<input class="groupkey" type="'.$debug.'" name="data[_groupkeys][]" autocomplete="off" value="'.$string.'">';
		$out .= '<input class="grouplabel" name="data[_groupvals][]" autocomplete="off" type="'.$debug.'" value="'.$label.'">';
		$out .= '<input class="groupdesc" name="data[_groupdescs][]" autocomplete="off" type="'.$debug.'" value="'.$description.'">';
	
	$out .= '</li>';

	return $out;
}

// group var wrapper template 
function get_vargroup($id, $data=null, $show=''){
	$vargroup = '<ul class="navigation-tabs vargroup" '.$show.' id="'.$id.'">';
		if(!empty($data)){
			$vargroup .= $data;
		}
	$vargroup .= '</ul>';
	return $vargroup;
}

//var item line
function get_varLine($varkey, $slug = '{{slug}}', $label='{{label}}',$group = '{{group}}', $tabgroup='{{tabgroup}}', $multi=0, $debug = 'hidden'){
	
	$vartemp = '<li id="'.$varkey.'">';
	$vartemp .= '<a id="conftab'.$varkey.'" href="#varconf'.$varkey.'" class="attributetab attributevar"><span id="linelabel'.$varkey.'">'.$label.'</span> <i class="selectarrow icon-chevron-right pull-right">&nbsp;</i></a>';
	// set Group
	$vartemp .= '<input class="tabgroup" type="'.$debug.'" autocomplete="off" value="'.$tabgroup.'" name="data[_tabgroup]['.$varkey.']">';
	$vartemp .= '<input class="multigroup" type="'.$debug.'" autocomplete="off" value="'.$group.'" name="data[_group]['.$varkey.']">';
	$vartemp .= '<input class="multivar" type="'.$debug.'" autocomplete="off" value="'.$multi.'" name="data[_isMultiple]['.$varkey.']">';
	$vartemp .= '</li>';

	return $vartemp;
}
// Var Config Template
function get_varTemplate($varkey, $label=null, $slug=null, $type='textfield', $info=null, $default=null, $display=true){
	global $types;

	$type = strtolower( str_replace(' ', '', $type ) );
	
	if(empty($show)){
		$show = 'style="display:none;"';
	}else{
		$show = '';
	}
	$varconfig = '<div class="confgroup" '.$show.' id="varconf'.$varkey.'">';
		$varconfig .= '<button class="button delete-attribute pull-right" data-reference="'.$varkey.'" type="button"><i class="icon-remove"></i></button>';
		$varconfig .= '<h3><span id="label'.$varkey.'">'.$label.'</span> <span id="slug'.$varkey.'" class="var-slugpreview cm-mustache">{{'.$slug.'}}</span></h3>';

				// NAME
		$varconfig .= '<div class="brickroad_configOption textfield">';
			$varconfig .= '<label>Field Label</label>';
			$varconfig .= '<input class="labeledit" type="text" value="'.$label.'" data-reference="'.$varkey.'" autocomplete="off" name="data[_label]['.$varkey.']">';
		$varconfig .= '</div>';

				// SLUG/VAR
		$varconfig .= '<div class="brickroad_configOption textfield">';
			$varconfig .= '<label>Variable / Slug</label>';
			$varconfig .= '<input class="slugedit" type="text" value="'.$slug.'" data-reference="'.$varkey.'" autocomplete="off" name="data[_variable]['.$varkey.']">';
		$varconfig .= '</div>';

				// INFO
		$varconfig .= '<div class="brickroad_configOption textfield">';
			$varconfig .= '<label>Field Help/Info Line</label>';
			$varconfig .= '<input class="infoedit" type="text" value="'.$info.'" data-reference="'.$varkey.'" autocomplete="off" name="data[_variableInfo]['.$varkey.']">';
		$varconfig .= '</div>';

				// TYPE
		$varconfig .= '<div class="brickroad_configOption textfield">';
			$varconfig .= '<label>Type</label>';
			$varconfig .= '<select class="typeedit" style="width: 142px;" id="type'.$varkey.'" data-reference="'.$varkey.'" autocomplete="off" name="data[_type]['.$varkey.']">';
				foreach($types as $fieldType=>$configType){

					$sel = '';

					if($type === $configType){
						$sel = 'selected="selected"';
					}

					if( !file_exists( BRICKROAD_PATH . 'templates/fields/field-' . $configType . '.php' ) && $configType != 'fieldelement'){
						$sel .= ' disabled="disabled"';	
						$fieldType .= ' (Not available in lite)';
					}

					$varconfig .= '<option '.$sel.' value="'.$configType.'">'.$fieldType.'</option>';
				}
			$varconfig .= '</select>';
		$varconfig .= '</div>';


		/*
				// Conditionals
		$varconfig .= '<div class="brickroad_configOption textfield">';
			$varconfig .= '<label for="condition'.$varkey.'">Enable Conditionals</label>';
			$varconfig .= '<input class="checkbox" id="condition'.$varkey.'" data-reference="'.$varkey.'" name="data[_conditions]['.$varkey.'][enabled]" type="checkbox">';
		$varconfig .= '</div>';

				// Conditionals setup
		$varconfig .= '<div class="brickroad_configOption textfield">';
			$varconfig .= '<label for="conditionlogic'.$varkey.'"></label>';
			$varconfig .= '<select class="doaction" style="width: 142px;" id="conditionlogic'.$varkey.'" data-reference="'.$varkey.'" autocomplete="off" name="data[_conditions]['.$varkey.'][do]">';
				$varconfig .= '<option value="hide">Hide Field</option>';
				$varconfig .= '<option value="show">Show Field</option>';
			$varconfig .= '</select> if';
		$varconfig .= '</div>';
				// Conditionals setup
		$varconfig .= '<div class="brickroad_configOption textfield">';
			$varconfig .= '<label for="conditionlogic'.$varkey.'"></label>';
			$varconfig .= '<select class="doaction" style="width: 142px;" id="conditionlogic'.$varkey.'" data-reference="'.$varkey.'" autocomplete="off" name="data[_conditions]['.$varkey.'][do]">';
				$varconfig .= '<option value="add">All</option>';
				$varconfig .= '<option value="show">Name</option>';
				$varconfig .= '<option value="show">Age</option>';
			$varconfig .= '</select>';
		$varconfig .= '</div>';

		$varconfig .= '<div class="brickroad_configOption textfield">';
			$varconfig .= '<label for="conditionlogic'.$varkey.'"></label>';
			$varconfig .= '<select class="doaction" style="width: 142px;" id="conditionlogic'.$varkey.'" data-reference="'.$varkey.'" autocomplete="off" name="data[_conditions]['.$varkey.'][do]">';
				$varconfig .= '<option value="add">Empty</option>';
				$varconfig .= '<option value="show">Has a value</option>';
				$varconfig .= '<option value="show">Equals</option>';
				$varconfig .= '<option value="show">Does not equal</option>';
			$varconfig .= '</select>';
		$varconfig .= '</div>';

		*/




		// DEFAULT / CONFIG
		$varconfig .= '<div class="brickroad_configOption textarea fieldtype-default fieldtype-config" >';
			$varconfig .= '<label style="display:block;">Default</label>';
			$varconfig .= '<textarea class="defaultedit" style="width:100%;" autocomplete="off" data-reference="'.$varkey.'" name="data[_variableDefault]['.$varkey.']">'.$default.'</textarea>';
		$varconfig .= '</div>';



		/// OPTION EDITOR TEMPLATE - DROPDOWN + CHECKBOXES
		$varconfig .= '<div class="fieldtype-wrap-onoff fieldtype-wrap-dropdown fieldtype-wrap-radio fieldtype-wrap-checkbox fieldtype-wrap-checkbox-inline fieldtype-wrap-radio-inline fieldtype-config">';
			$varconfig .= '<button class="button button-block dropdown-options-add-line" type="button">Add Option</button>';
			/// GET Option Line
			if(!empty($default)){
				$varconfig .= get_varOptionsTemplate($default);
			}
		$varconfig .= '</div>';

		/// POST TYPE SELECTOR TEMPLATE
		$varconfig .= '<div class="fieldtype-wrap-posttypeselector fieldtype-config">';
			$varconfig .= get_posttypeselectTemplate($default);
		$varconfig .= '</div>';

		/// slider config
		$varconfig .= '<div class="fieldtype-wrap-slider fieldtype-config">';
			$varconfig .= get_sliderconfigTemplate($default);
		$varconfig .= '</div>';

		/// fieldtype config
		$varconfig .= '<div class="fieldtype-wrap-fieldelement fieldtype-config">';
			$varconfig .= get_fieldtypeconfigTemplate($default);
		$varconfig .= '</div>';



	$varconfig .= '<div style="clear:both;"></div></div>';
return $varconfig;
}

// Var Config Template
function get_varOptionsTemplate($defaultstring=false){
	$defaults = array(0=>'');
	if(!empty($defaultstring)){
		$defaults = explode(',', $defaultstring);
	}
	$varconfig = '';
	foreach($defaults as &$default){
		$key = $default;
		$val = '';
		$sel = '';
		if(strpos($default, '*') !== false){
			$sel = 'checked="checked"';
			$default = str_replace('*', '', $default);
		}
		if(strpos($default, '||') !== false){
			$parts = explode('||', $default);
			$key = $parts[0];
			$val = $parts[1];
		}
		$varconfig .= '<div class="dropdown-options-editor-line">';
			$varconfig .= '<input type="checkbox" class="isinitial" title="default selected option" '.$sel.' autocomplete="off"> <input class="dropdown-options-editor dropdown-options-key textfield-small" placeholder="value" type="text" value="'.$key.'" autocomplete="off"> ';
			$varconfig .= '<input class="dropdown-options-editor dropdown-options-val textfield-small" placeholder="label" type="text" value="'.$val.'" autocomplete="off">';
			$varconfig .= '<button type="button" class="button remove-option-line pull-right"><i class="icon-remove"></i></button>';
		$varconfig .= '</div>';
	}
	return $varconfig;

}

// range selector config template
function get_sliderconfigTemplate($defaultstring = null){
	
	$default = 0;
	$min = 0;
	$max = 10;
	$suffix = '';

	$defaults = explode('|', $defaultstring);
	$range = explode(',', $defaults[0]);
	if(isset($range[1])){
		$min = $range[0];
		$max = $range[1];
	}
	if(isset($defaults[1])){
		$default = (int) $defaults[1];
	}
	if(isset($defaults[2])){
		$suffix = $defaults[2];
	}

	//$range = explode(',', $defaults[0]);


	$varconfig = '<div class="brickroad_configOption">';
		$varconfig .= '<label>Minimum Value</label>';
		$varconfig .= '<input class="slider-conf-val slider-min-val" type="text" value="'.$min.'" autocomplete="off" style="width:50px;">';
	$varconfig .= '</div>';
	
	$varconfig .= '<div class="brickroad_configOption">';
		$varconfig .= '<label>Max Value</label>';
		$varconfig .= '<input class="slider-conf-val slider-max-val" type="text" value="'.$max.'" autocomplete="off" style="width:50px;">';
	$varconfig .= '</div>';
	
	$varconfig .= '<div class="brickroad_configOption">';
		$varconfig .= '<label>Default Value</label>';
		$varconfig .= '<input class="slider-conf-val slider-default-val" type="text" value="'.$default.'" autocomplete="off" style="width:50px;">';
	$varconfig .= '</div>';
	
	$varconfig .= '<div class="brickroad_configOption">';
		$varconfig .= '<label>Suffix Value</label>';
		$varconfig .= '<input class="slider-conf-val slider-suffix-val" type="text" value="'.$suffix.'" autocomplete="off" style="width:50px;"> <span class="description">Range unit i.e px or km </span>';
	$varconfig .= '</div>';

	return $varconfig;
}


// post type selector template
function get_posttypeselectTemplate($defaultstring = null){
	
	$varconfig = '<div class="brickroad_configOption">';
	$varconfig .= '<label>Post Type</label>';
	$varconfig .= '<select class="post-type-select post-type-selected-type">';

	$post_types = get_post_types(array(), 'objects');

	$vals = explode('||', $defaultstring);
	if(empty($vals[0])){
		$vals[0] = null;
	}
	$dropdownindex = 0;
	foreach($post_types as $type=>$obj){
		$sel = null;
		if($type == $vals[0]){
			$sel = 'selected="selected"';
		}
		$varconfig .= '<option value="'.$type.'" '.$sel.'>'.$type.'</option>';
	}
	$varconfig .= '</select>';
	$varconfig .= '</div>';

	$varconfig .= '<div class="brickroad_configOption">';
		$varconfig .= '<label>Default ID</label>';
		$varconfig .= '<input class="post-type-select post-type-default-id textfield-small" type="text" value="'.(isset($vals[1]) ? $vals[1] : '').'" autocomplete="off">';
	$varconfig .= '</div>';

	return $varconfig;
}

// post type selector template
function get_fieldtypeconfigTemplate($defaultstring = null){
	
	$varconfig = '<div class="brickroad_configOption">';
	$varconfig .= '<label>Element</label>';
	$varconfig .= '<select class="field-type-select field-type-selected-type">';
	$varconfig .= '<option value=""></option>';
	$elements = get_option('BR_ELEMENTS');

	if(!empty($elements)){
		foreach($elements as $eid=>$element){
			if(isset($_GET['element'])){
				if($eid == $_GET['element']){
					continue;
				}
			}
			if($element['elementType'] == '20'){
				$sel = null;
				$varconfig .= '<option value="'.$eid.'" '.$sel.'>'.$element['name'].'</option>';
			}
		}
	}

	$varconfig .= '</select>';
	$varconfig .= '</div>';

	//$varconfig .= '<div class="brickroad_configOption">';
	//	$varconfig .= '<label>Default ID</label>';
	//	$varconfig .= '<input class="post-type-select post-type-default-id textfield-small" type="text" value="'.(isset($vals[1]) ? $vals[1] : '').'" autocomplete="off">';
	//$varconfig .= '</div>';

	return $varconfig;
}

// mini funciton to get the group
function att_getGroup($varkey, $Element){
	if($Element['_group'][$varkey] === $varkey){
		return $varkey;
	}else{
		return att_getGroup($Element['_group'][$varkey], $Element);
	}
}
// first we list the vars andwrap them in their groups!
$groups = array();
if(!empty($Element['_variable'])){
	foreach($Element['_variable'] as $varkey=>&$slug){
		// build list of groups
		if(!empty($Element['_isMultiple'][$varkey])){
			// is a multiple - will have its own group
			// check if its the mater or has a master
			$group = att_getGroup($varkey, $Element);
		}else{
			$group = $varkey;
		}
		$groups[$Element['_tabgroup'][$group]][] = $varkey;
	}
}

if( !file_exists( BRICKROAD_PATH . 'libs/element-modules/post_type.php' ) ){
	echo '<span style="color: #ff0000;">Not all field types are available in the Lite version. <a class="button-primary" href="http://cramer.co.za/upgrade" target="_blank">Learn more</a></span><hr>';
}

?>
<div id="var-groups">
	<div class="var-tools">
		<button type="button" id="add-var-group" class="button button-block">Add Group</button>
	</div>
	<ul class="navigation-tabs" id="groups-list">
		<?php
			$firstGroup = 'active';
			foreach ($groups as $groupLabel=>&$group) {
				//$panelID = uniqid('panel');
				$string = '';
				if(!empty($Element['_variable'])){
					$string = implode(',',$group);
				}
				$grouptype = 'icon-folder-close-alt';
				if(!empty($Element['_isMultiple'][$group[0]])){
					$grouptype = 'icon-repeat';
				}
				echo get_groupLine('group'.$group[0], $groupLabel, '', $string,$firstGroup, $grouptype);
				$firstGroup = '';
			}
		?>
	</ul>
</div>
<div id="var-list" <?php if(empty($groups)){ echo 'style="display:none;"';}; ?>>
	<div class="var-tools">
		<button type="button" id="add-group-var" class="button">New Attribute</button>
		<button class="button" id="group-config" type="button"><i class="icon-cog"></i></button>
	</div>
	<div class="var-tools group-options hidden">
		<input type="text" id="edit-group-name" value="" placeholder="edit group">
		<!-- <input type="text" id="edit-group-desc" value="" placeholder="group description"> -->
		<button class="button button-block" id="group-set-multiple" type="button"><i class="icon-repeat"></i> Repeatable</button>
	</div>
	<?php 
		$show = '';
		if(!empty($Element['_variable'])){
			foreach ($groups as $groupLabel=>&$group) {
				$groupvars = '';
				foreach ($group as $varkey) {
					$multi = 0;
					if(!empty($Element['_isMultiple'][$varkey])){
						$multi = 1;
					}
					$multigroup = '';
					if(!empty($Element['_group'][$varkey])){
						$multigroup = $Element['_group'][$varkey];
					}
					$groupvars .= get_varLine($varkey, $Element['_variable'][$varkey], $Element['_label'][$varkey], $multigroup, $Element['_tabgroup'][$varkey], $multi);
				}
				echo get_vargroup('group'.$group[0], $groupvars, $show);
				
				$show = 'style="display:none;"';
			}
		}
	?>
</div>
<div id="var-config">
	<?php
		$show = true;
		if(!empty($Element['_variable'])){
			foreach ($groups as $groupLabel=>&$group) {
				foreach ($group as $varkey) {
					echo get_varTemplate($varkey, $Element['_label'][$varkey], $Element['_variable'][$varkey], $Element['_type'][$varkey], $Element['_variableInfo'][$varkey], $Element['_variableDefault'][$varkey], $show);
					$show = false;
				}
			}
		}

	?>
</div>

<script type="text/javascript">

var deletegroupbuttontemplate = '<?php echo get_deleteGroup(); ?>';
var grouplinetemplate = '<?php echo get_groupLine('{{id}}', '{{group}}', '',''); ?>';
var vargrouptemplate = '<?php echo get_vargroup('{{id}}','','style="display:none;"'); ?>';
var conftemplate = '<?php echo get_varTemplate('{{id}}', '{{label}}', '{{slug}}'); ?>';
var varitemtemplate = '<?php echo get_varLine('{{id}}', '{{slug}}','{{label}}', '{{tabgroup}}', '{{group}}', ''); ?>';
var optionsLineTemplate = '<?php echo get_varOptionsTemplate(); ?>';
var posttypeTemplate = '<?php echo get_posttypeselectTemplate(); ?>';
var sliderTemplate = '<?php echo get_sliderconfigTemplate(); ?>';

jQuery(function($){
	toggleFieldConfigs();
})


</script>

















































