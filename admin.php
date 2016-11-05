<?php
global $shortcode_tags, $wp_filesystem;

// load elements
$Elements = get_option('BR_ELEMENTS');

?>
<div class="wrap poststuff" id="brickroad_container">
    <h2 style="display:none;"><!-- hack to force notices below here. not much in docs on how to do it properly. oh well. --></h2>
    <div id="brickroad_header" class="wrap">
        <div class="title">
            <h2><span class="dashicons dashicons-text"></span> <?php echo BRICKROAD_NAME; ?> <small><?php echo BRICKROAD_VER; ?></small></h2>
        </div>
        <div id="brickroadbanner">
            <a href="?page=brickroad-admin&action=edit" class="button">New Element</a>
            <a href="#exporter_screen" id="exporter" class="button">Export</a>
            <a href="#importer_screen" id="importer" class="button">Import</a>            
            <?php /*<a href="http://ce.caldera.co.za" target="_blank" class="button" id="docs">Knowledge Base & Support</a> */ ?>
            <a href="admin.php?page=brickroad-admin&intro=show" id="show_intro" class="button-primary">About</a>
            <?php /*<a href="https://twitter.com/dcramer" class="twitter-follow-button" data-show-count="false" data-dnt="true">Follow @dcramer</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>*/ ?>
        </div>
        <?php if(!empty($_GET['error']) && !empty($_GET['_error_nonce'])){ ?>
        <div class="wrap">
            <div class="updated" id="message"><p>Could not activate elements because it triggered a <strong>fatal error</strong>.</p>
                <iframe width="100%" height="70px" src="plugins.php?action=error_scrape&amp;plugin=brickroad-elements/plugincore.php&amp;_wpnonce=<?php echo $_GET['_error_nonce']; ?>" style="border:0"></iframe>
            </div>
        </div>
        <?php } ?>
    </div>

    <div id="main">
    <?php

        $url = wp_nonce_url('admin.php?page=brickroad-admin','brickroad-admin-element');
        if (false === ($creds = request_filesystem_credentials($url) ) ) {
            // if we get here, then we don't have credentials yet,
            // but have just produced a form for the user to fill in,
            // so stop processing for now
            echo '</div></div>';
            return true; // stop the normal page form from displaying
        }
        // now we have some credentials, try to get the wp_filesystem running
        if ( ! WP_Filesystem($creds) ) {
            // our credentials were no good, ask the user for them again
            request_filesystem_credentials($url);
            echo '</div></div>';
            return true;
        }

    if(!empty($Elements)){
        foreach($Elements as $ID=>$cfg){
            //clean up previews - need to be a transient per user.
            $preview_plugin_path = BRICKROAD_PATH . 'elements/' . sanitize_title( $ID );
            // kill off the preview to not keep it around.
            if ( file_exists( $preview_plugin_path ) ) {
                $wp_filesystem->delete( $preview_plugin_path, true );
            }
        }
    }


    ?>    
        <div id="ce-nav">
            <ul style="display:none;" id="docNav">
            </ul>
            <ul id="mainNav">
                <?php

                $hasactive = brickroad_detectRogues();
                $toUpdate = array();
                $elementsFound = array();

                if(!empty($Elements)){
                    foreach($Elements as $ID=>$cfg){

                        if(!isset($cfg['current_checksum'])){
                            $toUpdate[$ID] = true;
                        }
                        
                        $elementsFound[] = "'".$ID."'";
                    }
                }
                
                if(!empty($rogue)){
                    echo "<li class=\"current\">\n";
                    echo '<a title="Rogue Elements" href="#_elementrogues"><span class="cs-elementCount">'.count($rogue).'</span>Rogue Elements</a>'."\n";
                    echo "</li>\n";                    
                }
                if(!empty($toUpdate)){
                    $class = '';
                    if(empty($rogue)){
                        $class = 'current';
                    }
                    echo "<li class=\"current\">\n";
                    echo '<a title="Element Upgrades" href="#_elementupdates"><span class="cs-elementCount">'.count($toUpdate).'</span>Element Upgrades</a>'."\n";
                    echo "</li>\n";
                }

                $AlwaysLoads = get_option('CE_ALWAYSLOAD');
                $pages = array();
                if(empty($Elements)){
                    $Elements = array();
                }
                $allActive = array();
                foreach($Elements as $element=>$options){
                    if(!empty($options['category'])){
                        $pages[strtolower($options['category'])][] = $element;
                    }
                    if(!empty($options['state']) && empty($hasactive)){
                        $allActive[] = $element;
                    }
                    if(!empty($options['childof'])){
                        $children[$options['childof']][] = $element;
                    }
                }
                
                if(!empty($allActive)){
                    $pages['__All Active____'] = $allActive;
                }
                ksort($pages);
                $elementindex = 1;
                if(empty($pages)){
                    echo '<li class="current">';
                        echo '<a title="Elements" href="#Elements">Elements</a>';
                    echo '</li>';
                }
                $currentCat = false;
                if(!empty($_GET['cat'])){
                    $currentCat = $_GET['cat'];
                }
                if($currentCat == '__allactive____' && empty($allActive)){
                    $currentCat = false;
                }


                foreach($pages as $page=>$items){
                    $class = '';
                    $tabid = sanitize_key(strtolower($page));
                    if($elementindex === 1 && (empty($toUpdate) && empty($currentCat) && empty($rogue))){
                        $class = 'class="current"';
                    }
                    if(!empty($currentCat)){
                        if($currentCat == $tabid){
                            $class = 'class="current"';
                        }
                    }
                    

                ?>
                <li <?php echo $class; ?>>
                    <a title="<?php echo sanitize_title(str_replace('__', '<strong>', str_replace('____', '</strong>', ucwords($page)))); ?>" href="#cecat_<?php echo $tabid; ?>"><span class="cs-elementCount"><?php echo count($items); ?></span><span class="cat-title"><?php echo str_replace('__', '<strong>', str_replace('____', '</strong>', ucwords($page))); ?></span></a>
                </li>
                <?php
                    $elementindex++;
                }
                ?>

            </ul>

        </div>

        <div id="content">
            <div style="display: none;" class="group" id="importer_screen">
                <h2>Import Element</h2>
                <form action="?page=brickroad-admin" method="post" enctype="multipart/form-data" id="importerForm">
                    <?php
                        echo wp_nonce_field('cs-import-shortcode');
                    ?>
                    File <input type="file" name="import" style="border:none;" /><input class="button" type="submit" value="Import" />
                </form>
            </div>
            <div style="display: none;" class="group" id="exporter_screen">
                <h2>Exporter</h2>
                <form action="?page=brickroad-admin" method="post" id="exporterForm">
                    <ul class="tabs">
                        <li class="active"><a href="#exporterSettings">Export Settings</a></li>
                        <li><a href="#exporterElements">Elements</a></li>
                    </ul>
                    <div class="exporterSettings settingTab">
                        <div class="description"><p>Configure your export.</p></div>
                        <?php

                        $pluginExport = get_option('_brickroad_export_');

                        $user = wp_get_current_user();
                        if(empty($pluginExport)){
                            $pluginExport = array(
                                '_pluginName' => 'Exported Elements',
                                '_pluginURI' => '',
                                '_pluginDescription' => '',
                                '_pluginAuthor' => $user->data->display_name,
                                '_pluginAuthorEmail' => $user->data->user_email,
                                '_pluginVersion' => '1.00',
                                '_pluginAuthorURI' => $user->data->user_url,
                                '_includeWidget' => '2'
                            );
                            foreach($items as $Element){
                                $pluginExport['_'.$Element.'_toExport'] = 1;
                            }
                        }

                        echo wp_nonce_field('mspro-exoport-set');
                        
                        echo brickroad_configOption('pluginSet', 'pluginSet', 'hidden', 'PluginSet', array('_pluginSet'=>'_brickroad_export_'));
                        echo brickroad_configOption('pluginName', 'pluginName', 'textfield', 'Name', $pluginExport, 'Give a unique name');
                        echo brickroad_configOption('pluginURI', 'pluginURI', 'textfield', 'URL', $pluginExport, 'Set plugin\'s website.');
                        echo brickroad_configOption('pluginDescription', 'pluginDescription', 'textfield', 'Description', $pluginExport, 'Give a description');
                        echo brickroad_configOption('pluginAuthor', 'pluginAuthor', 'textfield', 'Author', $pluginExport, 'Set the authors name');
                        echo brickroad_configOption('pluginAuthorEmail', 'pluginAuthorEmail', 'textfield', 'Author Email', $pluginExport, 'Set the authors email address');
                        echo brickroad_configOption('pluginVersion', 'pluginVersion', 'textfield', 'Version', $pluginExport, 'Set the version number');
                        echo brickroad_configOption('pluginAuthorURI', 'pluginAuthorURI', 'textfield', 'Author URL', $pluginExport, 'Set the authors website url.');
                        
                        ?>
                    </div>
                    <div class="exporterElements settingTab" style="display:none;">
                        
                        <div class="description">
                            <p>Select which elements are to be included in the export.</p>
                            <?php
                            //echo brickroad_configOption('_phpToLibrary', '_phpToLibrary', 'checkbox', 'Export PHP Tab as a functions file.', $pluginExport, $Elements[$Element]['description']);
                            ?>
                        </div>
                        <?php
                        foreach($Elements as $EID=>$Element){
                            if($Element['state'] === 1){
                            ?>
                            <div id="config_<?php echo $tabid.$EID.'_toExport'; ?>" class="alert-error brickroad_configOption checkbox">
                                <label style="margin-left: 10px; width: 570px;" for="<?php echo $tabid.$EID.'_toExport'; ?>">
                                    <input type="checkbox" checked="checked" disabled="true"> <?php echo $Element['name']; ?> <span class="description">Active elements cannot be exported.</span>
                                </label>
                                <div class="brickroad_captionLine description"><?php echo $Element['description']; ?></div>
                            </div>

                            <?php
                            }else{
                            ?>
                            <div id="config_<?php echo $tabid.$EID.'_toExport'; ?>" class="brickroad_configOption checkbox">
                                <label style="margin-left: 10px; width: 570px;" for="<?php echo $tabid.$EID.'_toExport'; ?>">
                                    <input type="checkbox" checked="checked" value="<?php echo $EID; ?>" id="<?php echo $tabid.$EID.'_toExport'; ?>" name="data[export][]"> <?php echo $Element['name']; ?>
                                </label>
                                <div class="brickroad_captionLine description"><?php echo $Element['description']; ?></div>
                            </div>
                            <?php
                            }
                            //echo brickroad_configOption(, $Element.'_toExport', 'checkbox', $Elements[$Element]['name'], $pluginExport, $Elements[$Element]['description']);
                            //echo brickroad_configOption($Element.'_shortCodeOut', $Element.'_shortCodeOut', 'checkbox', 'Set element to export as shortcode', $pluginExport, 'This enables the element to be loaded as a shortcode via the shortcode inserter.');
                        }
                        ?>
                    </div>
                    <div class="exportbuttonbar">
                    <p>Exported Plugins & Theme Includes are exported to the Plugins folder & current active theme folder and not downloded. You can pick up the plugin via FTP. (downloads are coming)</p>
                        <select name="exportType">
                            <option value="plugin">Plugin</option>
                            <option value="theme">Theme Include</option>
                            <option value="script">Brickroad Import (json)</option>
                        </select>
                        <span class=""><button type="submit" value="plugin" class="button"><i class="icon-download-alt"></i> Export</button></span>
                    </div>
                </form>
            </div>
            <?php

            if(!empty($toUpdate)){
                echo '<div class="group" id="_elementupdates">';
                echo '<h2>Elements Upgrades</h2>';
                echo '<div class="description">Things have changed since the last update. These elements need to be upgraded to retain compatibility.</div>';
                    //echo '<ul>';
                    foreach($toUpdate as $updateID=>$val){
                        echo '<span class=""><div id="upg_'.$updateID.'" class="elementUpgradeNodes" style="float:left; padding:3px; border-radius:4px; background:#ed0000; color:#fff;margin:3px;">'.$Elements[$updateID]['name'].'</div></span>';
                    }
                    //echo '</ul>';
                    ?>
                        <div class="exportbuttonbar clear">

                            <span id="upgradeElementsButton" class="" onclick="brickroad_upgradeElements();"><button type="button" class="button"><i class="icon-ok-sign"></i> Upgrade Elements</button></span>
                        </div>
            <?php
                echo "</div>\n";
            }


            ?>
        <?php
        $index = 1;
        if(empty($pages)){
            echo '<div class="group" id="Elements"><h2>Elements</h2>Once you start creating elements, they will be listed here.</div>';
        }
        if(!empty($_GET['el'])){
            $currentElement = $_GET['el'];
        }
        foreach($pages as $page=>$items){
            $tabid = sanitize_key(strtolower($page));
            $Show = 'none';
            if($index === 1 && (empty($toUpdate) && empty($currentCat))){
                $Show = 'block';
            }
            if(!empty($currentCat)){
                if($currentCat == $tabid){
                    $Show = 'block';
                }
            }
            $fromActive = '';
            $prefix = '';
            if($tabid == '__allactive____'){
                $fromActive = '&from=active';
                $prefix = 'act';
            }

        ?>
            <div style="display: <?php echo $Show; ?>;" class="group elementcat" id="cecat_<?php echo $tabid; ?>">
                <?php if($tabid != '__allactive____'){ ?>
                <span class="exporter" style="float:right; padding: 0 3px 3px 3px;">
                    <a href="#<?php echo sanitize_key(strtolower($page)); ?>">
                        <button type="button" class="button button-small" id="addNewInterface">
                            <span style="margin-top:-1px;" class="icon-eye-open"></span> Export Group
                        </button>
                    </a>
                </span>
                <span class="manager" style="float:right; padding: 0 3px 3px 3px; display: none;">
                    <a href="#<?php echo sanitize_key(strtolower($page)); ?>">
                        <button type="button" class="button button-small" id="addNewInterface">
                            <span style="margin-top:-1px;" class="icon-eye-close"></span> Hide Export Config
                        </button>
                    </a>
                </span>
                <?php } ?>
                <h2><?php echo ucwords(str_replace('__', '', $page)); ?></h2>
                <?php if($tabid != '__allactive____'){ ?>
                <div class="catexport" style="display:none;">
                    <form action="?page=brickroad-admin" method="post" id="elementEditForm">
                        <ul class="tabs">
                            <li class="active"><a href="#pluginSettings">Plugin</a></li>
                            <li><a href="#elementSettings">Elements</a></li>
                        </ul>
                        <div class="pluginSettings settingTab">
                            <h2>Plugin Settings</h2>
                            <div class="description">This allows you to export this category of shortcodes as a standalone plugin.</div>
                            <?php

                            $pluginExport = get_option('_msp_'.sanitize_key($page));

                            $user = wp_get_current_user();
                            if(empty($pluginExport)){
                                $pluginExport = array(
                                    '_pluginName' => str_replace('__', '', $page),
                                    '_pluginURI' => '',
                                    '_pluginDescription' => '',
                                    '_pluginAuthor' => $user->data->display_name,
                                    '_pluginAuthorEmail' => $user->data->user_email,
                                    '_pluginVersion' => '1.00',
                                    '_pluginAuthorURI' => $user->data->user_url,
                                    '_includeWidget' => '2'
                                );
                                foreach($items as $Element){
                                    $pluginExport['_'.$Element.'_toExport'] = 1;
                                }
                            }

                            echo wp_nonce_field('mspro-exoport-set');
                            
                            echo brickroad_configOption('pluginSet', 'pluginSet', 'hidden', 'PluginSet', array('_pluginSet'=>sanitize_key($page)));
                            echo brickroad_configOption('pluginName', 'pluginName', 'textfield', 'Plugin Name', $pluginExport, 'Give the plugin a unique name');
                            echo brickroad_configOption('pluginURI', 'pluginURI', 'textfield', 'Plugin URL', $pluginExport, 'Set plugins website.');
                            echo brickroad_configOption('pluginDescription', 'pluginDescription', 'textfield', 'Plugin Description', $pluginExport, 'Give the plugin a description');
                            echo brickroad_configOption('pluginAuthor', 'pluginAuthor', 'textfield', 'Plugin Author', $pluginExport, 'Set the plugins author');
                            echo brickroad_configOption('pluginAuthorEmail', 'pluginAuthorEmail', 'textfield', 'Plugin Author Email', $pluginExport, 'Set the plugins authors email address');
                            echo brickroad_configOption('pluginVersion', 'pluginVersion', 'textfield', 'Plugin Version', $pluginExport, 'Set the version of this plugin');
                            echo brickroad_configOption('pluginAuthorURI', 'pluginAuthorURI', 'textfield', 'Plugin Author URL', $pluginExport, 'Set the Authors website address.');
                            
                            ?>
                        </div>
                        <div class="elementSettings settingTab" style="display:none;">
                            <h2>Elements to Export</h2>
                            <div class="description">                                
                                <p>This allows you to select which elements are exported.</p>
                                <?php
                                //echo brickroad_configOption('_phpToLibrary', '_phpToLibrary', 'checkbox', 'Export PHP Tab as a functions file.', $pluginExport, $Elements[$Element]['description']);
                                ?>
                            </div>
                            <?php

                            foreach($items as $Element){
                                 
                                if($Elements[$Element]['state'] === 1){
                                ?>
                                    <div class="alert-error brickroad_configOption checkbox">
                                        <label style="margin-left: 10px; width: 570px;" >
                                            <input type="checkbox" checked="checked" disabled="true"> <?php echo $Elements[$Element]['name']; ?> <span class="description">Active elements cannot be exported.</span>
                                        </label>
                                        <div class="brickroad_captionLine description"><?php echo $Elements[$Element]['description']; ?></div>
                                    </div>
                                <?php
                                }else{                                
                                    ?>
                                    <div id="config_<?php echo $tabid.$Element.'_toExport'; ?>" class="brickroad_configOption checkbox">
                                        <label style="margin-left: 10px; width: 570px;" for="<?php echo $tabid.$Element.'_toExport'; ?>">
                                            <input type="checkbox" checked="checked" value="<?php echo $Element; ?>" id="<?php echo $tabid.$Element.'_toExport'; ?>" name="data[export][]"> <?php echo $Elements[$Element]['name']; ?>
                                        </label>
                                        <div class="brickroad_captionLine description"><?php echo $Elements[$Element]['description']; ?></div>
                                    </div>
                                    <?php
                                }
                                //echo brickroad_configOption(, $Element.'_toExport', 'checkbox', $Elements[$Element]['name'], $pluginExport, $Elements[$Element]['description']);
                                //echo brickroad_configOption($Element.'_shortCodeOut', $Element.'_shortCodeOut', 'checkbox', 'Set element to export as shortcode', $pluginExport, 'This enables the element to be loaded as a shortcode via the shortcode inserter.');
                            }
                            ?>
                        </div>                       
                        <div class="exportbuttonbar">
                            <p>Exported Plugins & Theme Includes are exported to the Plugins folder & current active theme folder and not downloded. You can pick up the plugin via FTP. (downloads are coming)</p>
                            <select name="exportType">
                                <option value="plugin">WordPress Plugin</option>
                                <option value="theme">Theme Include</option>
                                <option value="script">Brickroad Import (json)</option>                            </select>
                            <span class=""><button type="submit" value="plugin" class="button"><i class="icon-download-alt"></i> Export</button></span>
                        </div>
                    </form>
                </div>
                <?php } ?>
                <div class="catbody">
                <?php
                $showpane = true;
                if(!empty($_GET['exporterror'])){
                    if($_GET['exporterror'] == sanitize_key(strtolower($page))){
                        echo '<div class="alert alert-error" onclick="jQuery(this).fadeOut();" style="cursor:pointer;">You did not select any elements to export.</div>';
                        $showpane = false;
                    }
                }
                if(!empty($Elements)){
                
                foreach($items as $Element){
                
                    $Options = $Elements[$Element];
                    $ShortCode = 'celement id='.$Element;
                    if(!empty($Options['shortcode'])){
                        $ShortCode = $Options['shortcode'];
                    }
                    if(empty($Options['description'])){
                        $Options['description'] = '';
                    }
                    $activeClass= '';
                    if(!empty($Options['state']) && empty($hasactive)){
                        $activeClass= 'active';
                    }

                    $icon = 'shortcode';
                    $toolTipType = 'Shortcode';
                    if(!empty($Options['elementType'])){
                        switch($Options['elementType']){

                            case '1':
                                $icon = 'shortcode';
                                $toolTipType = 'Shortcode';
                                break;
                            case '2':
                                $icon = 'iwidget';
                                $toolTipType = 'Widget';
                                break;
                            case '3':
                                $icon = 'hybrid';
                                $toolTipType = 'Hybrid (Widget & Shortcode)';
                                break;
                            case '4':
                                $icon = 'alwaysload';
                                $toolTipType = 'Global';
                                break;
                            case '5':
                                $icon = 'settings';
                                $toolTipType = 'Settings';
                                break;
                            case '6':
                                $icon = 'posttype';
                                $toolTipType = 'Post Type';
                                break;
                            case '7':
                                $icon = 'metabox';
                                $toolTipType = 'Metabox';
                                break;
                            case '8':
                                $icon = 'template';
                                $toolTipType = 'Template';
                                break;
                            case '9':
                                $icon = 'code';
                                $toolTipType = 'Class';
                                break;
                            case '10':
                                $icon = 'file';
                                $toolTipType = 'Element File';
                                break;
                            case '11':
                                $icon = 'foo';
                                $toolTipType = 'FooPlugins License';
                                break;
                            case '20':
                                $icon = 'fieldtype';
                                $toolTipType = 'Custom Field Type';
                                break;

                            //case '8':
                            //    $icon = 'posttype';
                            //    $toolTipType = 'Post Type';
                            //    break;

                        }
                    }
                    if(!empty($Options['posttype']) && file_exists( BRICKROAD_PATH . '/libs/element-modules/post_type.php' ) ){
                        $icon .= " post-type";
                    }

                    $isError = '';
                    $isLast = '';
                    $errorTitle = '';
                    $errorEnable = '';
                    if(!empty($shortcode_tags[strtolower($Options['shortcode'])]) && empty($Options['state'])){
                        if($shortcode_tags[strtolower($Options['shortcode'])] != 'brickroad_doShortcode'){
                            //echo $shortcode_tags[strtolower($Options['shortcode'])];
                            $isError = 'errorDetected';
                            $errorTitle = 'Shortcode is in use. You\'ll need to disable the plugin with the existing shortcode or change the element slug'; ;
                            $errorEnable = 'data-animation="true"';
                        }
                    }
                    if(!empty($currentElement)){
                        if($currentElement == $Element){
                            $isLast = 'lastEdited';
                        }
                    }
                    $rebuild = '';
                    if(isset($Options['current_checksum']) && isset($Options['active_checksum'])){
                        if($Options['current_checksum'] !== $Options['active_checksum']){                        
                            $rebuild = '<span class=""><a href="?page=brickroad-admin&action=activate&element='.$Element.$fromActive.'&rebuild=true"><span rel="tooltip" title="Rebuild Element" class="button button-primary" data-placement="right"><span class="icon-refresh" style="cursor:pointer;"></span></span></a></span>';
                        }else{
                            $rebuild = '<span class=""><a href="?page=brickroad-admin&action=activate&element='.$Element.$fromActive.'&rebuild=true"><span rel="tooltip" title="Rebuild Element" class="button" data-placement="right"><span class="icon-refresh" style="cursor:pointer;"></span></span></a></span>';
                        }
                    }
                    //switch()
                ?>
                <div id="element_<?php echo $Element; ?>" class="element_<?php echo $Element; ?>">
                <div class="cs-elementItem elementMain <?php echo $activeClass; ?> <?php echo $isError.' '.$isLast; ?>" title="<?php echo $errorTitle; ?>">
                    <div class="cs-elementInfoPanel pull-right buttons_<?php echo $Element; ?>">
                        <!-- <span class=""><span class="button infoTrigger" rel="<?php echo $Element; ?>" rel="tooltip" title="Show Details" data-placement="left"><i class="icon-info-sign"></i></span></span> -->
                        <?php
                        if($Options['elementType'] != '20'){
                            echo $rebuild;
                            if(!empty($isError)){
                            ?>
                            <span class=""><a><span rel="tooltip" title="Cannot activate as there is an error." class="button disabled <?php echo $activeClass; ?>" data-placement="right" <?php echo $errorEnable; ?>><span class="icon-ok-circle" style="cursor:pointer;"></span></span></a></span>
                            <?php
                            }else{
                            ?>
                            <span class=""><a href="?page=brickroad-admin&action=activate&element=<?php echo $Element.$fromActive; ?>"><span rel="tooltip" title="<?php if(!empty($activeClass)){ echo 'Deactivate'; }else{ echo 'Activate'; }; ?>" class="button <?php if(!empty($activeClass)){ echo 'button-primary'; }; ?>" data-placement="right"><span class="icon-ok-circle" style="cursor:pointer;"></span></span></a></span>
                            <?php
                            }
                        }
                        ?> &nbsp;&nbsp;
                        <span class=""><a href="?page=brickroad-admin&action=edit&element=<?php echo $Element; ?>"><div class="button" rel="tooltip" title="Edit Element" data-placement="left"><i class="icon-pencil"></i></div></a></span>
                        <span class=""><a href="?page=brickroad-admin&action=dup&element=<?php echo $Element; ?>"><div class="button" rel="tooltip" title="Duplicate Element" data-placement="left"><i class="icon-chevron-down"></i></div></a></span>
                        <span class=""><a href="#" class="confirm" rel="<?php echo $Element; ?>" onclick="return false;"><div class="button" rel="tooltip" title="Delete Element" data-placement="right"><i class="icon-remove-sign"></i></div></a></span>
                    </div>
                    <div id="confirm_<?php echo $Element; ?>" class="cs-elementInfoPanel buttons_<?php echo $Element; ?> pull-right" style="display:none;">
                        <span class=""><a href="#" onclick="brickroad_deleteElement('<?php echo $Element; ?>'); return false;"><div class="button-primary" rel="tooltip" title="Confirm Delete" data-placement="left"><i class="icon-ok"></i> Delete?</div></a></span>
                        <span class=""><a href="#" onclick="return false;" class="confirm" rel="<?php echo $Element; ?>"><div class="button"><i class="icon-share-alt"></i> Cancel</div></a></span>
                    </div>

                                        
                    <i class="<?php echo $icon; ?> icon-2x pull-left cs-elementIcon" rel="tooltip" title="<?php echo $toolTipType; ?>" data-placement="right"></i>
                    <h4><?php echo $Options['name']; ?></h4>
                    <div class="description"><?php echo $Options['description']; ?></div>
                
                <?php
                    

                    $example = '['.$ShortCode.' ';
       
                ?>
                    <div class="cs-infopanel options_<?php echo $Element; ?>">
                    <?php

                    if($Elements[$Element]['elementType'] == 1 || $Elements[$Element]['elementType'] == 3){

                    ?>

                        <h2>Shortcode</h2>
                        <?php if(!empty($Options['variables']['names'])){ ?>
                        <table width="100%" class="widefat">
                            <thead>
                                <tr>
                                    <th width="125">Attribute</th>
                                    <th width="125">Default</th>
                                    <th width="250">Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if(!empty($Options['variables']['names'])){
                                    foreach($Options['variables']['names'] as $Key=>$Variable){

                                        $example .= $Variable.'="'.$Options['variables']['defaults'][$Key].'" ';

                                    ?>
                                    <tr>
                                        <td width="125"><?php echo $Variable; ?></td>
                                        <td width="125"><?php echo $Options['variables']['defaults'][$Key]; ?></td>
                                        <td width="250"><?php echo $Options['variables']['info'][$Key]; ?></td>
                                    </tr>
                                    <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                            echo '<h2>Default Usage</h2>';
                        }
                            echo '['.$ShortCode.']';
                            if($Options['codeType'] == 2){
                                echo ' content [/'.$ShortCode.']';
                            }
                            if(!empty($Options['variables']['names'])){
                                echo '<h2>Full usage with defaults</h2>';
                                echo $example.']';
                                if($Options['codeType'] == 2){
                                    echo ' content [/'.$ShortCode.']';
                                }
                            }

                    }
                    if($Elements[$Element]['elementType'] == 2 || $Elements[$Element]['elementType'] == 3){
                                echo '<h2>Widget</h2>';
                                echo '<p>Once Avtivated, go to Apperance-><a href="widgets.php">Widgets</a> and select drag the My Shortcodes Pro widget into the side bar of choice.<p>';
                                echo '<p>Once in your side bar, select the category "<strong>'.$Elements[$Element]['category'].'</strong>" and the Element "<strong>'.$Elements[$Element]['name'].'</strong>" and click "Load Element".<p>';
                                echo '<p>You can now configure the element how you please.<p>';
                    }
                    if($Elements[$Element]['elementType'] == 4){
                                echo '<h2>Global</h2>';
                                if(!empty($Elements[$Element]['variables'])){
                                    echo '<p>Once Activated, go to Settings-><a href="options-general.php?page='.strtolower($Element).'">'.$Elements[$Element]['name'].'</a></p>';
                                    echo '<p>Here you can configure the plugins attributes to be loaded on every page.</p>';
                                }else{
                                    echo '<p>The plugin will load on every page and render the template in the location specified.</p>';
                                }                                
                    }
                    if($Elements[$Element]['elementType'] == 5){
                        echo '<h2>Code</h2>';
                        echo '<p>The Code Element allows you to programatically insert the element into your template.</p>';
                        echo '<p>To register the use of your Element, call the brickroad_register_element() function before the wp_head() call. This is registered in the header.php right at the top, but can be registered in the functions.php file if you so wish. Just remember to put in checks to ensure the element is registered on the correct page, else you\'ll end up having the scripts and styles loading on every page.</p>';
                                                        
                        
                        if(!empty($Elements[$Element]['variables'])){
                            echo '<p>Once Activated, go to Settings-><a href="options-general.php?page='.strtolower($Element).'">'.$Elements[$Element]['name'].'</a></p>';
                            echo '<p>Here you can configure the plugins attributes to be loaded on every page.</p>';
                        }else{
                            $colorCode = true;
                            //echo "<p class=\"php-code\">
                            echo '<h2>Register Element</h2>';
                            echo '<p>This queues up all the header scripts and libraries. You define the handle to identify it.</p>';
                            echo '<pre id="code'.$Element.'" class="codeColorElement cm-s-default">'."\n";
                            /*//echo "<?php\n";*/
                            echo "  ".'$args = array('."\n";
                            echo "      '_slug'=>'slug', \n";
                            if(!empty($Elements[$Element]['variables'])){
                                foreach($Elements[$Element]['variables']['names'] as $varkey=>$varval){
                                    echo "      '".$varval."'=>'".$Elements[$Element]['variables']['defaults'][$varkey]."',\n";
                                }
                            }
                            if($Elements[$Element]['codeType'] == 2){
                                echo "  '_content'=>'some content for the {{content}} tag'\n";
                            }
                            echo "  );\n";
                            echo "  brickroad_register_element('handle', ".'$args'.");\n";
                            /*echo "?>\n";*/

                            echo '</pre><h2>Call Element</h2>';
                            echo '<p>Once the element has been registered, you can call the render function to output the HTML.</p>';
                            echo '<p>In the location that you want the element rendered, simply call the following.</p>';
                            echo "<div class=\"codeColorElement cm-s-default\">echo brickroad_render_element('handle');</div>\n";
                        }
                    }

                        ?>
                    </div>
                    </div>
                  </div>
                <?php
                }}else{
                    echo 'You have no elements';
                }
                ?>

            </div>
        </div>
            <?php
            if(!empty($children[$Element])){
                foreach($children[$Element] as $childElement){
                    $Options = $Elements[$childElement];












?>
                <div id="element_<?php echo $Element; ?>" class="child">
                <div class="cs-elementItem">
                    <div class="cs-elementInfoPanel">
                        <?php echo $Options['name']; ?>
                        <div class="cs-elementInfoPanel description"><?php echo $Options['description']; ?></div>
                    </div>
                    <div class="cs-elementInfoPanel mid">Shortcode <?php if(!empty($Options['variables'])){ ?><span class="infoTrigger" rel="<?php echo $Element; ?>">Attributes</span><?php } ?>
                        <div class="cs-elementInfoPanel description">[<?php echo $ShortCode; ?>]</div>
                    </div>
                    <div id="" class="cs-elementInfoPanel last buttonbar buttons_<?php echo $Element; ?>" style="display:block;">
                        <span class=""><a href="?page=brickroad-admin&action=edit&element=<?php echo $Element; ?>"><div class="button"><span class="icon-pencil"></span></div></a></span>
                        <span class=""><a href="?page=brickroad-admin&action=edit&childof=<?php echo $Element; ?>"><div class="button"><span class="icon-indent-left"></span></div></a></span>
                        <span class=""><a href="#" class="confirm" rel="<?php echo $Element; ?>" onclick="return false;"><div class="button"><span class="icon-remove-sign"></span></div></a></span>
                    </div>
                    <div id="confirm_<?php echo $Element; ?>" class="cs-elementInfoPanel last buttons_<?php echo $Element; ?>" style="display:none;">
                        <div class="infoDelete">Delete?</div>
                        <span class=""><a href="#" onclick="brickroad_deleteElement('<?php echo $Element; ?>'); return false;"><div class="button-primary"><span class="icon-ok"></span></div></a></span>
                        <span class=""><a href="#" onclick="return false;" class="confirm" rel="<?php echo $Element; ?>"><div class="button"><span class="icon-share-alt"></span> Cancel</div></a></span>

                    </div>
                </div>
                <?php
                    if(!empty($Options['variables'])){

                    $example = '['.$ShortCode.' ';

                ?>
                    <div class="cs-infopanel cs-elementItem" id="options_<?php echo $Element; ?>">
                        <h2>Attributes</h2>
                        <table width="100%" class="widefat">
                            <thead>
                                <tr>
                                    <th width="125">Attribute</th>
                                    <th width="125">Default</th>
                                    <th width="250">Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach($Options['variables']['names'] as $Key=>$Variable){

                                    $example .= $Variable.'="'.$Options['variables']['defaults'][$Key].'" ';

                                ?>
                                <tr>
                                    <td width="125"><?php echo $Variable; ?></td>
                                    <td width="125"><?php echo $Options['variables']['defaults'][$Key]; ?></td>
                                    <td width="250"><?php echo $Options['variables']['info'][$Key]; ?></td>
                                </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php

                            echo '<h2>Default Usage</h2>';
                            echo '['.$ShortCode.']';
                            if($Options['codeType'] == 2){
                                echo ' content [/'.$ShortCode.']';
                            }
                            echo '<h2>Full usage with defaults</h2>';
                            echo $example.']';
                            if($Options['codeType'] == 2){
                                echo ' content [/'.$ShortCode.']';
                            }

                        ?>
                    </div>
                <?php
                    }
                ?>
                    </div>

<?php
























                }

            }

            $index++;
        }
            ?>



        </div>

        <div class="clear"></div>

    </div>

    <div style="clear:both;"></div>
</div>

<script type="text/javascript">

    function showElements(){
        jQuery('#mainNav').show();
        jQuery('#docNav').hide();
        jQuery('.group').hide();
        jQuery("#docs").show();
        jQuery("#elements").hide();
        
        jQuery('.elementcat').first().show();
        jQuery('#mainNav li').removeClass('current');
        jQuery('#mainNav li').first().addClass('current');
    }


    jQuery(document).ready(function(){

        jQuery('.confirm').click(function(){
            var ele = jQuery(this).attr('rel');
            jQuery('.buttons_'+ele).toggle();
        });
        jQuery('.infoTrigger').click(function(){
            jQuery(this).toggleClass('button-primary');
            jQuery('.options_'+jQuery(this).attr('rel')).slideToggle();
            if(jQuery(this).hasClass('button-primary')){
                jQuery(this).tooltip('hide').attr('data-original-title', 'Hide Details').tooltip('fixtitle').tooltip('show');
            }else{
                jQuery(this).attr('data-original-title', 'Show Details').tooltip('fixtitle').tooltip('show');
            }
        });
        jQuery('#ce-nav li a').click(function(){
            jQuery('#ce-nav li').removeClass('current');
            jQuery('.group').hide();
            jQuery(''+jQuery(this).attr('href')+'').show();
            jQuery(this).parent().addClass('current');
            return false;
        });
        jQuery('#importer,#explain,#exporter').click(function(){
            jQuery('#ce-nav li').removeClass('current');
            jQuery('.group').hide();
            jQuery(''+jQuery(this).attr('href')+'').show();
            jQuery(this).parent().addClass('current');
            return false;
        });
        

        jQuery('.exporter').click(function(e){
            e.preventDefault();
            jQuery(this).hide();
            jQuery(this).parent().find('.manager').show();
            jQuery(this).parent().find('.catbody').slideToggle();
            jQuery(this).parent().find('.catexport').slideToggle();
        })
        jQuery('.manager').click(function(e){
          e.preventDefault();
            jQuery(this).hide();
            jQuery(this).parent().find('.exporter').show();
            jQuery(this).parent().find('.catbody').slideToggle();
            jQuery(this).parent().find('.catexport').slideToggle();
        })
        jQuery('.tabs li a').click(function(){
            jQuery(this).parent().parent().find('.active').removeClass('active');
            jQuery(this).parent().addClass('active');
            jQuery(this).parent().parent().parent().find('.settingTab').hide();
            jQuery('.'+jQuery(this).attr('href').substring(1)).show();
            return false;
        })

      if(window.location.hash){
        var hash = window.location.hash.substring(1);
        if(hash.substring(0,1) != '!'){
            jQuery('.current').removeClass('current');

            var vals = hash.split('&');        

            jQuery('a[href="#'+vals[0]+'"]').parent().addClass('current');
            jQuery('#content .group').hide();
            jQuery('#'+vals[0]).show();
            jQuery('#element_'+vals[1]+' .cs-elementItem.elementMain').addClass('lastEdited');
        }
        //jQuery('.lastEdited').tooltip({title: 'Last Edited', placement: 'top'});

        //alert (hash);
      }

        jQuery( ".cs-elementItem" ).draggable({
            cursor: "move",
            distance: 20,
            cursorAt: { top: 10, left: -10 },
            helper: function( event ) {
                    return jQuery( "<div class='cs-elementItem' style='height:20px; padding: 5px 10px; z-index:10000;'>Drag to new Category</div>" );
            }
        });
        jQuery( "#ce-nav li" ).droppable({
            drop: function( event, ui ) {
                if(jQuery(this).find('a').attr('title') == '__All Active____'){
                    alert('You can activate the element by clicking the activate button.');
                }else{
                    brickroad_moveElement(jQuery(ui.draggable).parent().attr('id'), jQuery(this).find('.cat-title').html());
                    var num = parseFloat(jQuery(this).find('.cs-elementCount').html())+1;
                    jQuery(this).find('.cs-elementCount').html(num);
                    var lastnum = parseFloat(jQuery(this).parent().find('[href="#'+jQuery(ui.draggable).parent().parent().parent().attr('id')+'"]').prev().html())-1;
                    if(lastnum === 0){
                        jQuery(this).parent().find('[href="#'+jQuery(ui.draggable).parent().parent().parent().attr('id')+'"]').parent().slideUp();
                    }else{
                        jQuery(this).parent().find('[href="#'+jQuery(ui.draggable).parent().parent().parent().attr('id')+'"]').prev().html(lastnum);
                    }
                    jQuery(ui.draggable).parent().appendTo(jQuery(this).find('a').attr('href')+' .catbody');
                }
            }
        });
        <?php
        if(empty($settings['disableTooltips'])){
        ?>
        jQuery('.button, .cs-elementIcon').tooltip({animation: false});
        jQuery('.errorDetected').tooltip();
        <?php
        }
        ?>
        jQuery('#tooltipToggle').click(function(){

            var checkVal = jQuery(this).attr('checked');
            
            if(checkVal){
                jQuery('.button, .cs-elementIcon').tooltip('disable');
                jQuery('.errorDetected').tooltip('disable');
            }else{
                jQuery('.button, .cs-elementIcon').tooltip({animation: false});
                jQuery('.errorDetected').tooltip();
                jQuery('.button, .cs-elementIcon').tooltip('enable');
                jQuery('.errorDetected').tooltip('enable');
            }

            brickroad_setToolTips(checkVal);
        });
        
        
    });
</script>