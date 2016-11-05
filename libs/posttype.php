<div id="posttypePane"><?php
global $shortcode_tags;

if( !file_exists( BRICKROAD_PATH . 'libs/element-modules/post_type.php' ) ){
	echo '<span style="color: #ff0000;">Sorry, Post Types are not available in the Lite version. These settings will have no effect. <a class="button-primary" href="http://cramer.co.za/upgrade" target="_blank">Learn more</a></span><hr>';
}


echo brickroad_configOption('posttype', 'posttype', 'checkbox', 'Set element as a post type', $Element, 'This allows you to store pre-made elements. Very useful for elements with large config options.');


$showPostTypeOptions = 'style="display:none;"';
if(!empty($Element['_posttype'])){
	$showPostTypeOptions = '';
}
echo '<div id="posttype-wrapp" '.$showPostTypeOptions.'>';
echo brickroad_configOption('singleName', 'singleName', 'textfield', 'Singlular label', $Element);
echo brickroad_configOption('pluralName', 'pluralName', 'textfield', 'Plural label', $Element);
echo brickroad_configOption('menuicon', 'menuicon', 'textfield', 'Dashicon', $Element, 'Icon name from the Dashicons from 3.8', '><input type="button" data-target="#menuicon" class="button dashicons-picker" value="Choose Icon" ');
echo brickroad_configOption('showinmenu', 'showinmenu', 'textfield', 'Show in menu', $Element, 'If under another menu, place the location here. leave blank if parent.');
echo brickroad_configOption('menuposition', 'menuposition', 'textfield', 'Menu position', $Element, 'Numerical value of menu item position in the admin menu.');


$supports = array('author', 'thumbnail', 'excerpt', 'comments');
echo '<h3>Supports</h3>';
if(!empty($supports)){
	foreach($supports as $name=>$support){
		//dump($tax,0);
		echo brickroad_configOption('support_'.$support, 'support_'.$support, 'checkbox', ucwords($support), $Element);
	}
}
echo '<br>';


echo '<h3>Template</h3>';
echo brickroad_configOption('browsable', 'browsable', 'checkbox', 'Browsable', $Element, 'Allow this element to be public browsable. Like an actual post, removing the use as a shortcode.');



$showPostTypeBrowse = 'style="display:none;"';
if(!empty($Element['_browsable'])){
	$showPostTypeBrowse = '';
}
echo '<div id="posttype-template-options" '.$showPostTypeBrowse.'>';
	if(empty($Element['_template'])){
		$Element['_template'] = 'custom';
	}
	$droptions = array(
		"custom" => 'Theme controlled (archive-{post_type}.php, single-{post_type}.php)',
		"posts" => 'Posts (index.php, single.php)'
	);
	echo brickroad_configOption('template', 'template', 'dropdown', 'Template', $Element, 'Select the template behaviour.', $droptions);
	/*
	$showElTemp = ' style="display:none;"';
	if($Element['_template'] === 'element'){
		$showElTemp = '';
	}
	//if($Element['template'])
	echo '<div id="element-template-wrap" '.$showElTemp.'>';
		echo brickroad_configOption('templateElement', 'templateElement', 'textfield', 'Template Element Slug', $Element, 'The <strong>Code Type</strong> Element\'s slug (shortcode) to use as a template.', 'autocomplete="off"');
		?>
		<div class="alert">
			By using an element as a template, only the element is rendered. It's treated as a fully fledged template file.<br>
			Use the content of single.php or index.php from an existing template in the HTML tab of the template element as a starting point.<br>
			This will give you all the requirements to start your template, like get_header() and 'the loop' etc.
		</div>
		<?php
	echo '</div>';
	*/
echo '</div>';

echo '<h3>Taxonomies</h3>';
$taxo = get_taxonomies('','objects');
if(!empty($taxo)){
	foreach($taxo as $name=>$tax){
		//dump($tax,0);
		echo brickroad_configOption('tax_'.$tax->name, 'tax_'.$tax->name, 'checkbox', $tax->label.' <span class="description">('.$tax->name.')</span>', $Element);
	}
}


echo '<h3>Labels</h3>';
echo '<p class="description">Set the labels to be used for your post type. See <a href="http://codex.wordpress.org/Function_Reference/register_post_type#Arguments" target="_blank">WordPress Codex</a> for more.</p>';
$defaultLabels = array(
	'name' 				=> '{{_name}}',
	'singular name' 	=> '{{_singleName}}',
	'add new' 			=> 'Add New',
	'add new item' 		=> 'Add New {{_singleName}}',
	'edit item' 		=> 'Edit {{_singleName}}',
	'all items' 		=> 'All {{_pluralName}}',
	'view item' 		=> 'View {{_singleName}}',
	'search items' 		=> 'Search {{_pluralName}}',
	'not found' 		=> 'No {{_pluralName}} defined',
	'not found in trash'=> 'No {{_pluralName}} in trash',
	'parent item colon' => '',
	'menu name' 		=> '{{_pluralName}}',
);
foreach($defaultLabels as $label=>$value){
	$labelkey = str_replace('-', '_', sanitize_title( $label ) );


	if(!empty($Element['_post_type_label'][$labelkey])){
		$value = htmlentities( $Element['_post_type_label'][$labelkey] );
	}

	echo "<div id=\"config_posttype_label_" . $labelkey . "\" class=\"brickroad_configOption textfield\"><label>" . ucfirst( $label ) . "</label>\r\n";
		
		echo "<input type=\"text\" value=\"" . $value . "\" id=\"posttype_label_" . $labelkey . "\" name=\"data[_post_type_label][" . $labelkey . "]\">\r\n";

	echo "</div>\r\n";

}

echo '</div>';



?>
</div>
<script type="text/javascript">
	jQuery(function($){
		$('#template').on('change', function(){
			if(this.value === 'element'){
				$('#element-template-wrap').fadeIn(200);
			}else{
				$('#element-template-wrap').fadeOut(200);
			}
		});
		jQuery('#posttype').on('change', function(){
			if(jQuery(this).prop('checked')){
				jQuery('#posttype-wrapp').fadeIn(200);
			}else{
				jQuery('#posttype-wrapp').fadeOut(200);
			}			
		});
		jQuery('#browsable').on('change', function(){
			if(jQuery(this).prop('checked')){
				jQuery('#posttype-template-options').fadeIn(200);
			}else{
				jQuery('#posttype-template-options').fadeOut(200);
			}
		})

    <?php echo "var eles = new Array(".implode(',', $eles).");\n"; ?>

    jQuery('#templateElement').typeahead({source: eles});

	});
</script>