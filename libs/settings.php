<div id="settingsPane" class="config"><?php
global $shortcode_tags;
$Elements = get_option('BR_ELEMENTS');
$cats = array();
$eles = array();
if(!empty($Elements)){
    foreach($Elements as $keyy=>&$el){
        $cat = strtolower($el['category']);
        $cats[$cat] = '"'.$cat.'"';
        if($el['elementType'] === '5'){
            $eles[] = '"'.$el['shortcode'].'"';
        }
    }
}

if(!empty($Element['_shortcode'])){
    if(!empty($shortcode_tags[$Element['_shortcode']])){
        if($shortcode_tags[$Element['_shortcode']] == 'brickroad_doShortcode'){
            unset($shortcode_tags[$Element['_shortcode']]);
        }
    }
}

//vardump($shortcode_tags);
/* 
 * element settings
 * 
 */
if(empty($Element['_defaultContent'])){
    $Element['_defaultContent'] = '';
}
if(empty($Element['_shortcodeType'])){
    $Element['_shortcodeType'] = '1';
}
if(!isset($Element['_ID'])){
    $Element['_ID'] = '';
}

echo brickroad_configOption('ID', 'ID', 'hidden', 'element ID', $Element);

echo brickroad_configOption('name', 'name', 'textfield', 'Element Name', $Element);
echo brickroad_configOption('description', 'description', 'textfield', 'Element Description', $Element);

echo brickroad_configOption('category', 'category', 'textfield', 'Category', $Element, false, 'autocomplete="off"');

echo brickroad_configOption('shortcode', 'shortcode', 'textfield', 'Slug', $Element);


echo '<h3>Element Type</h3>';
$elementTypes = array(
    1 => 'Shortcode',
    2 => 'Widget',
    3 => 'Hybrid (Shortcode & Widget)',
    4 => 'Global',
    5 => 'Admin Page / Settings',
    6 => 'Post Type',
    7 => 'Metabox',
    9 => 'Class',
    10 => 'Element File',
    20 => 'Field Type',
);

$processorsFiles = array(
    1 => 'shortcode.php',
    2 => 'widget.php',
    3 => 'widget.php',
    4 => 'alwaysload-settings.php',
    5 => 'alwaysload-settings.php',
    6 => 'post_type.php',
    7 => 'metabox.php',
    9 => 'class.php',
    20 => 'fieldtype.php',
);


//echo brickroad_configOption('elementType', 'elementType', 'dropdown', 'Element Type', $Element, '', $elementTypes);
echo "<div id=\"config_elementType\" class=\"brickroad_configOption dropdown\">\r\n";
    echo "<label>Element Type</label>\r\n";
    echo "<select id=\"elementType\" name=\"data[_elementType]\">\r\n";

        foreach($elementTypes as $etype=>$elabel){
            
            $sel = '';
            if(!empty($Element['_elementType'])){
                if($etype == $Element['_elementType']){
                    $sel = ' selected="selected" ';
                }
            }
            if( !file_exists( BRICKROAD_PATH . 'libs/element-modules/' . $processorsFiles[$etype] ) ){
                $elabel .= ' (Not available in Lite)';                
                $sel .= ' disabled="disabled"'; 
            }


            echo "<option " . $sel . " value=\"" . $etype . "\">" . $elabel . "</option>\r\n";
        
        }

    echo "</select>\r\n";
echo "</div>\r\n";

echo brickroad_configOption('alwaysLoadPlacement', 'alwaysLoadPlacement', 'radio', 'Template Placement|Disable Template*, Header, Prepend Content, Append Content, Footer', $Element);


echo brickroad_configOption('widgetWrap', 'widgetWrap', 'radio', 'Use Widget Style|No,Yes*', $Element, 'Use Themes Widget Style');
echo brickroad_configOption('widgetTitle', 'widgetTitle', 'radio', 'Widget Title Field|No,Yes*', $Element);
echo brickroad_configOption('shortcodeType', 'shortcodeType', 'radio', 'Content Box|No*,Yes', $Element);
echo brickroad_configOption('defaultContent', 'defaultContent', 'textfield', 'Default Content', $Element);


// option for template type

echo brickroad_configOption('template_depth', 'template_depth', 'radio', 'Template level|*Content,Full Page', $Element);
echo brickroad_configOption('template_type', 'template_type', 'radio', 'Template type|*Single,Archive, Both', $Element);



// start the select - using the $args['id'] and $args['name']
//dump($post_types);

// Loop through all post types
$post_types = get_post_types(array(), 'objects');
$postTypes = array();

echo '<div id="config_for_type">';
echo brickroad_configOption('meta_context', 'meta_context', 'dropdown', 'Context', $Element, '', array('normal'=>'Normal','advanced'=>'Advanced','side'=>'Side'));
echo brickroad_configOption('meta_priority', 'meta_priority', 'dropdown', 'Priority', $Element, '', array('default'=>'Default', 'core'=>'Core', 'high'=>'High', 'low'=>'Low'));
echo brickroad_configOption('box_type', 'box_type', 'dropdown', 'Box Type', $Element, '', array('posttype'=>'Post-Type','dashboard'=>'Dashboard'));
echo brickroad_configOption('meta_build', 'meta_build', 'dropdown', 'Build Type', $Element, '', array('attributes'=>'Attributes','template'=>'Template'));
echo brickroad_configOption('meta_store', 'meta_store', 'dropdown', 'Meta Storage', $Element, '', array('array'=>'Attribute Array','single'=>'Single Values'));


echo '<div id="config_metaPostType" class="brickroad_configOption">';
    echo '<label class="multiLable">Supported Types</label>';

        $sel = '';
        if(isset($Element['_metabox_posttype']['dashboard'])){
            $sel = 'checked="checked"';
        }
        echo '<div class="toggleConfigOption hidden" >';
        echo '  <input type="checkbox" '.$sel.' value="dashboard" id="posttype_dashboard" name="data[_metabox_posttype][dashboard]"> <label style="width:auto;" for="posttype_dashboard">Dashboard <span class="description">dashboard</span></label>';
        echo '</div>';

    foreach($post_types as $type){
            if($type->name == $Element['_shortcode']){continue;}
        //if(!empty($type->public)){
            $sel = "";
            if(isset($Element['_metabox_posttype'][$type->name])){
                $sel = 'checked="checked"';
            }
            //$postTypes[$type->name] = $type->labels->name;
            //echo brickroad_configOption('for_type_'.$type->name, 'for_type][', 'checkbox', $type->name.' <span class="description">('.$type->labels->name.')</span>', $Element);
            echo '<div class="toggleConfigOption">';
            echo '  <input type="checkbox" '.$sel.' value="'.$type->name.'" id="posttype_'.$type->name.'" name="data[_metabox_posttype]['.$type->name.']"> <label style="width:auto;" for="posttype_'.$type->name.'">'.$type->labels->name.' <span class="description">'.$type->name.'</span></label>';
            echo '</div>';
        //}
    }
    echo '</div>';
echo '</div>';
echo '<div id="config_templatePostType" class="brickroad_configOption">';
    echo '<label class="multiLable">Supported Types</label>';
    foreach($post_types as $type){
            if($type->name == $Element['_shortcode']){continue;}
        //if(!empty($type->public)){
            $sel = "";
            if(isset($Element['_template_posttype'][$type->name])){
                $sel = 'checked="checked"';
            }
            //$postTypes[$type->name] = $type->labels->name;
            //echo brickroad_configOption('for_type_'.$type->name, 'for_type][', 'checkbox', $type->name.' <span class="description">('.$type->labels->name.')</span>', $Element);
            echo '<div class="toggleConfigOption">';
            echo '  <input type="checkbox" '.$sel.' value="'.$type->name.'" id="template_posttype_'.$type->name.'" name="data[_template_posttype]['.$type->name.']"> <label style="width:auto;" for="template_posttype_'.$type->name.'">'.$type->labels->name.'</label>';
            echo '</div>';
        //}
    }
echo '</div>';

echo '<h3 id="attrSettingsPage">Attribute Settings Page</h3>';
echo '<div id="config_elementLocations" class="brickroad_configOption">';

$elementLocations = array(
    'parent'                    => 'Parent Item',
    'index.php'                 => 'Dashboard',
    'edit.php'                  => 'Posts',
    'upload.php'                => 'Media',
    'edit.php?post_type=page'   => 'Pages',
    'edit-comments.php'         => 'Comments',
    'themes.php'                => 'Appearance',
    'plugins.php'               => 'Plugins',
    'users.php'                 => 'Users',
    'tools.php'                 => 'Tools',
    'options-general.php'       => 'Settings',
    'custom'                    => 'Custom'
);
echo brickroad_configOption('settingsParent', 'settingsParent', 'dropdown', 'Menu Location', $Element, '', $elementLocations);
echo brickroad_configOption('customLocation', 'customLocation', 'textfield', 'Custom Parent', $Element, 'Parent menu slug. e.g. edit.php?post_type=portfolio');
echo brickroad_configOption('settingsIcon', 'settingsIcon', 'textfield', 'Dashicon', $Element, 'Icon name from the Dashicons from 3.8', '><input type="button" data-target="#settingsIcon" class="button dashicons-picker" value="Choose Icon" ');
echo brickroad_configOption('settings_build', 'settings_build', 'dropdown', 'Build Type', $Element, '', array('attributes'=>'Attributes','template'=>'Template'));
    
    echo '<div id="settings_build_struct_wrap">';
        echo "<h4>Template Page</h4>";
        echo '<p>You have two options:<p>';
        echo "<p><strong>Auto</strong>: Automatically creates the wrapper for the page(Header, Title, Settings Wrapper, Save Button.). You simply need to add in your fields and layout.<br> Field names can be created with <code>name=\"_[slug]_options[field_name]\"</code> and can be retrieved via the <code>\$instance</code> array.</p>";
        echo "<p><strong>Blank</strong>: Nothing is added. You'll need to add your own management for saving and submitting.</p>";
        echo brickroad_configOption('settings_build_struct', 'settings_build_struct', 'dropdown', 'Settings Sections', $Element, '', array('auto'=>'Auto','blank'=>'Blank Template'));
    echo '</div>';

echo '</div>';

echo '<div id="element_type_wrap">';
    echo '<p class="description">File will be located in "includes/element-[slug].html". File will be .php type if php tags are used in the template.</p>';    
echo '</div>';

//echo brickroad_configOption('for_type', 'for_type', 'radio', 'Post Types|'.implode(',',$postTypes), $Element, 'The post type this template will be used for', $postTypes);

/*
                    'before_widget' => '<li id="%1$s" class="widget %2$s">',
                    'after_widget' => '</li>',
                    'before_title' => '<h2 class="widgettitle">',
                    'after_title' => '</h2>',
*/
                    /*
if(!isset($Element['_before_widget'])){
    $Element['_before_widget'] = '<li id="%1$s" class="widget %2$s">';
}
if(!isset($Element['_after_widget'])){
    $Element['_after_widget'] = '</li>';
}
if(!isset($Element['_before_title'])){
    $Element['_before_title'] = '<h2 class="widgettitle">';
}
if(!isset($Element['_after_title'])){
    $Element['_after_title'] = '</h2>';
}
if(!isset($Element['_widget_class'])){
    $Element['_widget_class'] = '';
}
echo '<div id="sidebar_helper">';
echo '<div class="alert alert-error">IMPORTANT: you need to place <strong>{{content}}</strong> in your HTML tab to mark the position in which load the widgets.</div>';
echo '<h3>Sidebar Wrapping</h3>';
echo '<p>Same as <code>register_sidebar();</code>. See the <a href="http://codex.wordpress.org/Function_Reference/register_sidebar#Default_Usage" target="_blank">WordPress Codex</a> for more.</p>';
echo brickroad_configOption('widget_class', 'widget_class', 'textfield', 'Class', $Element, 'Class to apply to widget HTML.');
echo brickroad_configOption('before_widget', 'before_widget', 'textfield', 'Before widget', $Element, 'HTML before widget');
echo brickroad_configOption('after_widget', 'after_widget', 'textfield', 'After widget', $Element, 'HTML after widget');
echo brickroad_configOption('before_title', 'before_title', 'textfield', 'Before widget', $Element, 'HTML before widget');
echo brickroad_configOption('after_title', 'after_title', 'textfield', 'After widget', $Element, 'HTML after widget');
echo '</div>';

echo brickroad_configOption('body_classes', 'body_classes', 'textfield', 'Custom body classes', $Element, 'Add custom classes to the body tag whenver this element is used on the page.');
*/

echo '<h3>Processing</h3>';
echo brickroad_configOption('removelinebreaks', 'removelinebreaks', 'checkbox', 'Remove linebreaks (Disable wp_autop)', $Element);


?>
<script>

    jQuery('#tabid1').click(function(){
        jQuery('#editorPane .tabs a').removeClass('active');
        jQuery(this).addClass('active');
        jQuery('#editorPane .area article').hide();
        jQuery(jQuery(this).attr('href')).show();
    });

    
    
    function toggle_elementConfigs(){
        jQuery('#attrSettingsPage, #config_templatePostType, #posttype-tab, #nocodes, #config_for_type, #config_template_type, #config_template_depth, #config_alwaysLoadPlacement, #config_shortcodeType, #config_defaultContent, #config_widgetTitle, #config_widgetWrap, #config_customLocation, #config_elementLocations, #config_settingsIcon').hide();
        jQuery('#htmleditortab, #phpeditortab, #csseditortab, #jseditortab, #attribbutes-tab, #preview-toggle').show();


        ///

        var elementType = jQuery('#elementType').val();

        if(elementType == '1'){
            jQuery('#config_shortcodeType').show();
            jQuery('#posttype-tab').show();
        }
        if(elementType == '2'){
            jQuery('#config_shortcodeType').show();
            jQuery('#config_widgetTitle').show();
            jQuery('#config_widgetWrap').show();
            jQuery('#config_defaultContent').show();
            jQuery('#posttype-tab').show();
        }
        if(elementType == '3'){
            jQuery('#config_shortcodeType').show();
            jQuery('#config_widgetTitle').show();
            jQuery('#config_widgetWrap').show();
            jQuery('#posttype-tab').show();
        }
        if(elementType == '4'){
            jQuery('#config_alwaysLoadPlacement').show();
            
            if(jQuery('.groupkey').length){
                jQuery('#config_elementLocations').show();
                jQuery('#attrSettingsPage').show();

            }else{
                jQuery('#config_elementLocations').hide();
                jQuery('#attrSettingsPage').hide();                
            }

            if( jQuery('#settingsParent').val() === 'custom'){
                jQuery('#config_customLocation').show();
            }else if(jQuery('#settingsParent').val() === 'parent'){
                jQuery('#config_settingsIcon').show();
                jQuery('#config_customLocation').hide();
            }else{
                jQuery('#config_settingsIcon').hide();
                jQuery('#config_customLocation').hide();
            }

        }
        if(jQuery('#shortcodeType_2').prop('checked')){
            jQuery('#config_defaultContent').show();
        }
        if(elementType == '5'){
            jQuery('#htmleditortab').hide();
            jQuery('#preview-toggle').hide();
            jQuery('#preview-atts').hide();
            jQuery('#csseditortab').hide();
            jQuery('#jseditortab').hide();
            jQuery('#nocodes').show();
            jQuery('#config_elementLocations').show();
            if( jQuery('#settingsParent').val() === 'custom'){
                jQuery('#config_customLocation').show();
            }else if(jQuery('#settingsParent').val() === 'parent'){
                jQuery('#config_settingsIcon').show();
                jQuery('#config_customLocation').hide();
            }else{
                jQuery('#config_settingsIcon').hide();
                jQuery('#config_customLocation').hide();
            }

            if(jQuery('#settings_build').val() == 'attributes'){
                jQuery('#htmleditortab').hide();
                jQuery('#preview-toggle').hide();
                jQuery('#settings_build_struct_wrap').hide();
            }else{
                jQuery('#preview-toggle').show();
                jQuery('#attribbutes-tab, #nocodes').hide();
                jQuery('#htmleditortab').show();
                jQuery('#csseditortab').show();
                jQuery('#jseditortab').show();
                jQuery('#settings_build_struct_wrap').show();
            }            
        }
        /*
        if(jQuery('#elementType_7').prop('checked')){
            //jQuery('#htmleditortab').hide();
            jQuery('#config_before_widget').show();
            jQuery('#config_after_widget').show();
            jQuery('#config_before_title').show();
            jQuery('#config_after_title').show();
            jQuery('#sidebar_helper').show();
        }else{
            jQuery('#config_before_widget').hide();
            jQuery('#config_after_widget').hide();
            jQuery('#config_before_title').hide();
            jQuery('#config_after_title').hide();
            jQuery('#sidebar_helper').hide();
        }*/
        if(elementType == '6'){
            jQuery('#config_shortcodeType').show();
            jQuery('#posttype-tab').show();
            jQuery('#posttype').prop('checked', true);
            jQuery('#posttype-wrapp').fadeIn(200);
            jQuery('#config_posttype').hide();
        }else{
            jQuery('#config_posttype').show();
            if(elementType > '3'){
                jQuery('#posttype').prop('checked', false);
                jQuery('#posttype-wrapp').hide();
            }
            
        }
        if(elementType == '7'){
            jQuery('#config_for_type').show();
            if(jQuery('#box_type').val() == 'dashboard'){
               jQuery('#config_metaPostType').hide();
               //jQuery('#config_meta_build').hide();
               jQuery('#config_metaPostType').find('input').prop('checked', false);
               jQuery('#posttype_dashboard').prop('checked', true);
            }else{
                jQuery('#posttype_dashboard').prop('checked', false);
                //jQuery('#config_meta_build').show();
                jQuery('#config_metaPostType').show();
                if(jQuery('#meta_build').val() == 'attributes'){
                    jQuery('#htmleditortab').hide();
                    jQuery('#preview-toggle').hide();
                    //jQuery('#config_meta_build').show();
                }else{
                    jQuery('#attribbutes-tab').hide();
                    //jQuery('#config_meta_build').hide();
                }
            }
        }
        if(elementType == '8'){
            jQuery('#config_templatePostType').show();
            jQuery('#config_template_type').show();
            jQuery('#config_template_depth').show();
        }
        if(elementType == '9'){
            jQuery('#htmleditortab').hide();
            jQuery('#preview-toggle').hide();
            jQuery('#csseditortab').hide();
            jQuery('#jseditortab').hide();
            jQuery('#attribbutes-tab').hide();
            jQuery('#libs-tab').hide();
            jQuery('#assets-tab').hide();
        }
        if(elementType == '10'){
            jQuery('#htmleditortab').show();
            jQuery('#csseditortab').hide();
            jQuery('#jseditortab').hide();
            jQuery('#attribbutes-tab').hide();
            jQuery('#libs-tab').hide();
            jQuery('#assets-tab').hide();
            jQuery('#phpeditortab').hide();
            jQuery('#element_type_wrap').show();

        }else{
            jQuery('#element_type_wrap').hide();
        }
        if(elementType == '20'){
            jQuery('#phpeditortab').hide();
        }
        

        /*
        if(jQuery('#for_type').val() === 'page'){
            jQuery('#template_type_1').attr('checked', true);
            jQuery('#template_depth_2').attr('checked', true);
            jQuery('#template_type_2').attr('disabled','disabled');
            jQuery('#template_type_3').attr('disabled','disabled');
            jQuery('#template_depth_1').attr('disabled','disabled');
        };*/

    }

    //shortcodeType
    jQuery("input[name='data[_shortcodeType]']").change(function(e){
        ////console.log(this);
        if(this.value == 2){
            jQuery('#config_defaultContent').show();
        }else{
            jQuery('#config_defaultContent').hide();
        }
    });
    jQuery("#elementType, #meta_build, #settings_build, #box_type, #settingsParent").change(function(e){
            toggle_elementConfigs();
    });


    jQuery("input[name='data[_alwaysLoadPlacement]']").change(function(e){
        if(jQuery('#alwaysLoadPlacement_1').attr('checked')){
            jQuery('#tab_ctb_3').fadeOut();
        }else{
            jQuery('#tab_ctb_3').fadeIn();
        }
    });
    jQuery('#for_type').change(function(){
        if(this.value == 'page'){
            jQuery('#template_type_1').attr('checked', true);
            jQuery('#template_depth_2').attr('checked', true);
            jQuery('#template_type_2').attr('disabled','disabled');
            jQuery('#template_type_3').attr('disabled','disabled');
            jQuery('#template_depth_1').attr('disabled','disabled');
        }else{
            jQuery('#template_type_2').removeAttr('disabled');
            jQuery('#template_type_3').removeAttr('disabled');
            jQuery('#template_depth_1').removeAttr('disabled');
        }
    });

    <?php
    $usedCodes = array();
    foreach($shortcode_tags as $code=>$func){
        $usedCodes[] = '"'.$code.'"';
    }

    echo "var cats = new Array(".implode(',', $cats).");\n";
    echo "var used = new Array(".implode(',', $usedCodes).");\n";

    ?>
    if(jQuery.inArray(jQuery('#shortcode').val(), used) >= 0){
            jQuery('#SCerrorMessage').remove();
            jQuery('#shortcode').css('borderColor', '#ff0000');
            jQuery('#shortcode').after(' <span id="SCerrorMessage" class="description">This shortcode is already in use and will cause problems</span>');
    }
    jQuery('#shortcode').keyup(function(){
        if(jQuery.inArray(this.value, used) >= 0){
            jQuery('#SCerrorMessage').remove();
            jQuery('#shortcode').css('borderColor', '#ff0000');
            jQuery('#shortcode').after(' <span id="SCerrorMessage" class="description">This shortcode is already in use and will cause problems</span>');
        }else{
            jQuery('#shortcode').css('borderColor', '');
            jQuery('#SCerrorMessage').remove();
        }
    })


    

    jQuery('#category').typeahead({source: cats});
    jQuery(function(){
        toggle_elementConfigs();
    })
    
</script></div>