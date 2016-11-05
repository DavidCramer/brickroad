<?php
	if(empty($Element['__showpreview__'])){
		//$previewSRC = site_url().'?myshortcodeproinsert=preview&code='.$Element['_ID'].'';
	}
/*<iframe id="previewOutput" style="width: 100%;" src="<?php echo $previewSRC; ?>"></iframe>*/

?>
<iframe id="previewOutput" style="width: 100%;"></iframe>
<input type="hidden" id="setShowPreview" name="data[__showpreview__]" value="<?php echo $Element['__showpreview__']; ?>" />
<script type="text/javascript">
	
	var fieldscripts =[], fieldstyles = [];
	
	function brickroad_reloadPreview(eid, nosave, preview, saveatts){
			htmleditor.save();
			csseditor.save();
			phpeditor.save();
			jseditor.save();
			var debugmode = '';
			
			//var ifsrc = jQuery('#previewOutput').attr('src').split('&rdm');
			if(nosave){
			   // jQuery('#previewOutput').attr('src', ifsrc[0]+'&rdm='+Math.floor((Math.random()*10000)+1));
				return;
			}else{
				jQuery('#saveIndicator').fadeIn(200);
				var data = {
						action: 'apply_element',
						EID: eid,
						formData: jQuery('#editor-form').serialize()
					},
					atts_setup = jQuery('#attributes-preview-setup');

				if(preview){
					data.preview = true;
				}
				
				if(saveatts){
					data.saveatts = true;
					data.preview = null;
				}

				jQuery.post(ajaxurl, data, function(response) {
					

					//var newtitle = response;
					//if(jQuery('#name').val().length > 0){
					//    newtitle = jQuery('#name').val();
					//}
					//jQuery('#elementTitle').html(newtitle);
					//if(jQuery('.preview-pane').is(':visible')){
					 //   jQuery('#previewOutput').attr('src', ifsrc[0]+'&rdm='+Math.floor((Math.random()*10000)+1));
					//}
					jQuery('#saveIndicator').fadeOut(200);
					if(typeof response === 'object'){
						var scriptlink = jQuery('.brickroad-admin-panel-script'),
							stylelink = jQuery('.brickroad-admin-panel-style');


						for(var style in response.style){
							if(!fieldstyles[style]){
								jQuery('head').append('<link rel="stylesheet" id="preview_style_' + style + '" class="brickroad-admin-panel-style" type="text/css" href="' + response.style[style] + '">');
								fieldstyles[style] = true;
							}
						};

						for(var script in response.script){
							if(!fieldscripts[script]){
								var scripttag = jQuery('<script>', {
									'id': 'preview_script_'+script,
									'class': 'brickroad-admin-panel-script',
									'type': 'text/javascript',
									'src': response.script[script]
								});
								scripttag.appendTo('body');
								fieldscripts[script] = true;
							}
						};
						
						atts_setup.html(response.html).show();
					}else{
						atts_setup.html('').hide();
						jQuery('#preview-atts').removeClass('button-primary');
						var idoc = document.getElementById('previewOutput');
						if( jQuery( idoc ).is(':visible') ){
							idoc.contentDocument.open();
							idoc.contentDocument.write(response);
							idoc.contentDocument.close();
						}
					}
					
				});
			}

	};

	jQuery('document').ready(function($){
		brickroad_reloadPreview('<?php echo $Element['_ID']; ?>');
	});

	
</script>