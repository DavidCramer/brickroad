<?php
		$Title = 'New Element';
		$Element = array();
		$Element['_ID'] = strtoupper(uniqid('EL'));
		if(!empty($_GET['element'])){
				$Element = get_option($_GET['element']);
				$Title = $Element['_name'];				
		}else{				
				$Element['_shortcode'] = '';//uniqid();//;$Element['_ID'];
				$Element['_elementType'] = '1';
		}
		if(!isset($Element['__showpreview__'])){
				$Element['__showpreview__'] = 0;
		}
		// remove admin bar for preview
		$user = wp_get_current_user();
		/*$ismenu = get_user_meta($user->ID, 'show_admin_bar_front', true);
		if($ismenu == true){
				update_user_meta($user->ID, 'show_admin_bar_front', false);
				$disableadminbarmessage = 'We disabled your admin bar for preview. You can enable it again in your profile settings.';
		}*/
		$showPostType = 'style="display:none;"';
		if($Element['_elementType'] === '1' || $Element['_elementType'] === '2' || $Element['_elementType'] === '3' || $Element['_elementType'] === '6'){
			$showPostType = '';
		}
?>
		<form action="?page=brickroad-admin" method="post" id="editor-form">
		<?php wp_nonce_field('cs-edit-shortcode'); ?>
				<div class="header-nav">
						<div class="brickroad-logo-icon"><span class="dashicons dashicons-text"></span></div>
						<ul class="editor-section-tabs navigation-tabs">
								<li><span><strong id="element-title"><?php echo $Title; ?></strong> - <span id="element-slug-line"><?php echo (!empty($Element['_shortcode']) ? $Element['_shortcode'] : $Element['_ID']); ?></span></span></li>
								<li class="divider-vertical"></li>
								<li id="htmleditortab" <?php echo ($Element['_elementType'] != '5' ? '' : ' style="display:none;"'); ?>><a href="#html">Template</a></li>
								<li id="phpeditortab" <?php echo ($Element['_elementType'] != '5' ? '' : ' style="display:none;"'); ?>><a href="#php">Functions</a></li>
								<li id="csseditortab" <?php echo ($Element['_elementType'] != '5' ? '' : ' style="display:none;"'); ?>><a href="#css">Styles</a></li>
								<li id="jseditortab" <?php echo ($Element['_elementType'] != '5' ? '' : ' style="display:none;"'); ?>><a href="#js">Javascript</a></li>
								<li id="nocodes" <?php echo ($Element['_elementType'] == '5' ? '' : ' style="display:none;"'); ?>><a href="#attributes">Attributes</a></li>
								<li class="divider-vertical"></li>
						</ul>
						<div class="nav-tools">
							<a href="#" id="element-apply" type="button" class="button">Apply</a>&nbsp;
							<button type="submit" class="button">Save & Close</button>&nbsp;
							<a href="?page=brickroad-admin&action=edit" class="button">New Element</a>&nbsp;
							<button id="preview-toggle" type="button" class="button <?php if(!empty($Element['__showpreview__'])){ echo 'button-primary'; } ?>">Preview</button>
							<button href="#" id="preview-atts" type="button" class="button">Preview Attributes</button>&nbsp;
							<span id="saveIndicator" class="spinner" style="display: inline-block; float:none;">&nbsp;</span>
							<button id="zen-toggle" type="button" class="button pull-right"><i class="icon-fullscreen"></i></button>
						</div>

				</div>
				<div class="side-controls">
						<ul class="element-config-tabs navigation-tabs">
								<li class="active"><a class="control-settings-icon left" href="#config" title="Settings"><span>Settings</span></a></li>
								<li id="posttype-tab" <?php echo $showPostType; ?>><a class="control-posttype-icon left" href="#post-type" title="Post Type"><span>Post Type</span></a></li>
								<li id="attribbutes-tab"><a class="control-attributes-icon active left" href="#attributes" title="Attributes"><span>Attributes</span></a></li>
								<!-- <li id="pods-tab"><a class="control-pods-icon active left" href="#pods" title="Pods"><span>Pods</span></a></li> -->
								<li id="libs-tab"><a class="control-libraries-icon left" href="#libraries" title="Libraries"><span>Libraries</span></a></li>
								<li id="assets-tab"><a class="control-assets-icon left" href="#assets" title="Assets"><span>Assets</span></a></li>
						</ul>
				</div>
				<div class="editor-pane" style="right:0;">            
						<div id="config" class="editor-tab active editor-setting editor-config">
								<div class="editor-tab-content">
										<h3>Settings <small>Element settings and display options</small></h3>
										<?php include BRICKROAD_PATH . 'libs/settings.php'; ?>
								</div>
						</div>
						<?php /*
						<div id="hooks" class="editor-tab editor-setting editor-hook">
								<div class="editor-tab-content">
										<h3>Hooks <small>Define Actions and Filters</small></h3>
										<?php include BRICKROAD_PATH . 'libs/hooks.php'; ?>
								</div>
						</div>
						<div id="pods" class="editor-tab editor-setting editor-pods">
								<div class="editor-tab-content">
										<h3>Pods <small>Use Pods as a Data Source</small></h3>
										<?php include BRICKROAD_PATH . 'libs/pods.php'; ?>
								</div>
						</div>
						*/
						?>
						<div id="attributes" class="editor-tab editor-setting editor-attributes">
								<div class="editor-tab-content">
										<h3>Attributes <small>Element variables and attributes</small></h3>                    
										<?php include BRICKROAD_PATH . 'libs/variables.php'; ?>
								</div>
						</div>
						<div id="post-type" class="editor-tab editor-setting editor-posttype">
								<div class="editor-tab-content">
										<h3>Post Type <small>Configure post type </small></h3>
										<?php include BRICKROAD_PATH . 'libs/posttype.php'; ?>
								</div>
						</div>
						<div id="libraries" class="editor-tab editor-setting editor-libraries">
								<div class="editor-tab-content">
										<h3>Libraries <small>Scripts and styles to be included in the header</small></h3>                    
										<?php include BRICKROAD_PATH . 'libs/libraries.php'; ?>
								</div>
						</div>
						<div id="assets" class="editor-tab editor-setting editor-assets">
								<div class="editor-tab-content">
										<h3>Assets <small>Additional files and scripts to be used by your element.</small></h3>                    
										<?php include BRICKROAD_PATH . 'libs/assets.php'; ?>
								</div>
						</div>
						<div id="php" class="editor-tab editor-code editor-php">
								<label for="code-php">PHP</label>
								<textarea id="code-php" name="data[_phpCode]"><?php if(!empty($Element['_phpCode'])){ echo htmlspecialchars($Element['_phpCode']); } ;?></textarea>
						</div>
						<div id="css" class="editor-tab editor-code editor-css">
								<label for="code-css">CSS</label>
								<textarea id="code-css" name="data[_cssCode]"><?php if(!empty($Element['_cssCode'])){ echo $Element['_cssCode']; } ;?></textarea>
						</div>
						<div id="html" class="editor-tab editor-code editor-html">
								<label for="code-html">HTML</label>
								<textarea id="code-html" name="data[_mainCode]"><?php if(!empty($Element['_mainCode'])){ echo htmlspecialchars($Element['_mainCode']); } ;?></textarea>
						</div>
						<div id="js" class="editor-tab editor-code editor-js">
								<label for="code-js">JavaScript</label>
								<textarea id="code-js" name="data[_javascriptCode]"><?php if(!empty($Element['_javascriptCode'])){ echo $Element['_javascriptCode']; } ;?></textarea>
						</div>            
				</div>
				<?php
				/*
				<div class="editor-revisions">
						<div class="editor-tab-content">
								<h3>Revisions</h3>
						</div>            
				</div>
				 */
				?>
				<div id="preview-iframe-holder" class="preview-pane editor-preview" style="display:none;">
						<label>Result</label>
						<?php include BRICKROAD_PATH . 'libs/preview.php'; ?>
				</div>
				<div id="attributes-preview-setup" class="preview-pane atts-setup-pane" style="display:none;">
					<div id="attributes-preview-setup-wrapper"></div>
				</div>
		</form>

		<?php

		//build sidebar elements list
		$sides = array();
		foreach($Elements as $sbid=>&$isSide){
			$insertTypes = array(
				"Code Element" => 5,
				"Sidebar" => 7
				);
			if(in_array($isSide['elementType'], $insertTypes) && $sbid != $Element['_ID']){
				$sides[] = $isSide['shortcode']."||".$isSide['name']." (".array_search($isSide['elementType'], $insertTypes).")";
			}
		}
		if(!empty($sides)){
			echo '<input type="hidden" value="'.implode(',',$sides).'" id="element_sidebars">';
		}
		?>
				<script type="text/javascript">

						/* Apply Element Changes & Reload Preview */
						jQuery('#element-apply').click(function(e){
							e.preventDefault();
							brickroad_reloadPreview('<?php echo $Element['_ID']; ?>');
						});
						jQuery('#preview-atts').click(function(e){
							var clicked = jQuery(this);
							if( clicked.hasClass('button-primary')){
								clicked.removeClass('button-primary');
								jQuery('#attributes-preview-setup').hide();
							}else{
								clicked.addClass('button-primary');
								brickroad_reloadPreview('<?php echo $Element['_ID']; ?>', false, true);
							}
						});
						jQuery('body').on('click','.save-att-config', function(){							
							brickroad_reloadPreview('<?php echo $Element['_ID']; ?>', false, true, true);
						});
						function brickroad_togglepreview(){
							if(jQuery('#preview-iframe-holder').hasClass('noshow')){
								return;
							}
								if(jQuery('.editor-pane').css('right') == '0px'){
										jQuery('.editor-pane').css({right: '50%'});
										jQuery('#setShowPreview').val('1');
										jQuery('#preview-toggle').addClass('button-primary');
										jQuery('#preview-iframe-holder').show();
								}else{
										jQuery('.editor-pane').css({right: 0});
										jQuery('#setShowPreview').val('0');
										jQuery('#preview-toggle').removeClass('button-primary');
										jQuery('#preview-iframe-holder').hide();
								}
								
								
								phpeditor.refresh();
								csseditor.refresh();
								htmleditor.refresh();
								jseditor.refresh();                
						}

						/* ready calls */
						/*
						var fields = [],
							magics = [
								"_id_",
								"content",
								"post_title",
								"post_status",
								"post_author",
								"post_date"
							];*/

						jQuery(document).ready(function(){

							jQuery('#name').on('change', function(){
								var slugline = jQuery('#shortcode'),
									slugged = this.value.replace(/[^a-z0-9\|\:]/gi, '_').toLowerCase();

								if(!slugline.val().length || slugline.val() === slugged){
									slugline.val(slugged);
								}
								jQuery('#element-title').html(this.value);
							});
							jQuery('#shortcode').on('change', function(){
								var setval = this.value;
								if(!setval.length){
									setval = jQuery('#name').val();
								}
								var slug = setval.replace(/[^a-z0-9\|\:]/gi, '_').toLowerCase();
								this.value = slug;
								jQuery('#element-slug-line').html(slug);
							});

							/* Bind ctr+s & cmd+s for saving*/

							
							jQuery('.slugedit').each(function(){
								//fields.push(jQuery(this).val());
							});
							////console.log(fields);

							jQuery(window).keypress(function(event) {
									if (!(event.which == 115 && event.metaKey) && !(event.which == 19)) return true;
									
									event.preventDefault();
									brickroad_reloadPreview('<?php echo $Element['_ID']; ?>');
									//brickroad_applyElement('<?php echo $Element['_ID']; ?>');
									return false;
							});
						});
						
				</script>




















