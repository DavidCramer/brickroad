(function() {
	"use strict";

	var Pos         = CodeMirror.Pos;

	function getFields(cm, options) {

		var cur = cm.getCursor(), token = cm.getTokenAt(cur),			
			result = [],
			fields = options.fields;
			switch (options.mode){
				case 'mustache':
					var wrap = {start: "{{", end: "}}"},
						prefix = token.string.slice(2);
					break;
				case 'cssMustache':
				case 'jsMustache':
					var wrap = {start: "", end: "}}"},
						prefix = token.string;
					break;
			}
		for( var field in fields){			
			if (field.indexOf(prefix) == 0 || prefix === '{'){
				if(prefix === '{'){
					wrap.start = '{';
				}
				result.push({text: wrap.start + field + wrap.end, displayText: fields[field]});
			}
		};

		return {
			list: result,
			from: Pos(cur.line, token.start),
			to: Pos(cur.line, token.end)
		};
	}
	CodeMirror.registerHelper("hint", "elementfield", getFields);
})();

function tagFields(cm, e) {
var cur = cm.getCursor();
	if (!cm.state.completionActive || e.keyCode === 18){			
		var cur = cm.getCursor(), token = cm.getTokenAt(cur), prefix,
		prefix = token.string.slice(0);
		if(prefix){
			if(token.type === 'mustache'){
				var fields = {};
				jQuery('.slugedit').each(function(){
					var field = jQuery(this).val();
					fields[field] = field;
				});
				jQuery('.assetlabel').each(function(){
					var field = jQuery(this).val();
					fields[field] = field + " (asset)";
				});
				// defaults
				fields["_id_"] = "id";
				fields["_index_"] = "index";
				fields["content"] = "content";
				fields["post_title"] = "post_title";
				fields["post_date"] = "post_date";
				//sidebar elements
				/*
				var sides = jQuery('#element_sidebars');
				if(sides.length){
					var sidebars = sides.val().split(',');
					jQuery.each(sidebars, function(k,v){
						var ellabel = v.split('||');

						fields[":"+ellabel[0]+":"] = ellabel[1];
					});
				}*/
				mode = cm.getMode();
				
			    CodeMirror.showHint(cm, CodeMirror.hint.elementfield, {fields: fields, mode: mode.name});

			}
		}
	}
	return;
}

						/* Setup Editors */

						var mustache = function(stream, state) {
									var ch;

								    /*if(fields.length > 0){
								        CodeMirror.xmlHints['{'] = [''].concat(fields.concat(magics));
								        for(f=0;f<fields.length;f++){
								            if (stream.match("{"+fields[f]+"}")) {
								                return "magic-at";
								            }
								        };
								    }else{
								        CodeMirror.xmlHints['{'] = magics;
								    }*/

									if (stream.match("{{_")) {
										while ((ch = stream.next()) != null)
											if (ch == "_" && stream.next() == '}' && stream.peek(stream.pos+2) == '}') break;
										stream.eat("}");
										return "mustacheinternal";
									}                  
									if (stream.match("{{")) {

										while ((ch = stream.next()) != null)
											if (ch == "}" && stream.next() == "}") break;
										stream.eat("}");
										return "mustache";
									}
									if (stream.match("[once]") || stream.match("[/once]") || stream.match("[/loop]") || stream.match("[else]") || stream.match("[/if]")) {
										return "command";
									}
									if (stream.match("[loop") || stream.match("[if")) {
										while ((ch = stream.next()) != null){
											if(stream.eat("]")) break;
										}
										return "command";
									}

									/*
									if (stream.match("[[")) {
										while ((ch = stream.next()) != null)
											if (ch == "]" && stream.next() == "]") break;
										stream.eat("]");
										return "include";
									}*/
									while (stream.next() != null && 
											!stream.match("{{", false) && 
											!stream.match("[[", false) && 
											!stream.match("{{_", false) && 
											!stream.match("[once]", false) && 
											!stream.match("[/once]", false) && 
											!stream.match("[loop", false) && 
											!stream.match("[/loop]", false) && 
											!stream.match("[if", false) && 
											!stream.match("[else]", false) && 
											!stream.match("[/if]", false) ) {}
									return null;
								};
						/*
					    if(fields.length > 0){
					        CodeMirror.xmlHints['{'] = [''].concat(fields.concat(magics));
					        for(f=0;f<fields.length;f++){
					            if (stream.match("{"+fields[f]+"}")) {
					                return "magic-at";
					            }
					        };
					    }else{
					        CodeMirror.xmlHints['{'] = magics;
					    }
					    */
						
						var phpeditor = CodeMirror.fromTextArea(document.getElementById("code-php"), {
							lineNumbers: true,
							matchBrackets: true,
							mode: "text/x-php",
							indentUnit: 4,
							indentWithTabs: true,
							enterMode: "keep",
							tabMode: "shift",
							lineWrapping: true
						});
						
						CodeMirror.defineMode("cssCode", function(config) {
							return CodeMirror.multiplexingMode(
								CodeMirror.getMode(config, "text/css"),
								{open: "<?php echo '<?php';?>", close: "<?php echo '?>';?>",
								 mode: CodeMirror.getMode(config, "text/x-php"),
								 delimStyle: "phptag"}
							);
						});
						CodeMirror.defineMode("cssMustache", function(config, parserConfig) {
							var mustacheOverlay = {
								token: mustache
							};
							return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "cssCode"), mustacheOverlay);
						});            
						var csseditor = CodeMirror.fromTextArea(document.getElementById("code-css"), {
							lineNumbers: true,
							matchBrackets: true,
							mode: "cssMustache",
							indentUnit: 4,
							indentWithTabs: true,
							enterMode: "keep",
							tabMode: "shift",
							lineWrapping: true
						});
						
						CodeMirror.defineMode("mustache", function(config, parserConfig) {
							var mustacheOverlay = {
								token: mustache
							};
							return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "application/x-httpd-php"), mustacheOverlay);
						});
						
						var htmleditor = CodeMirror.fromTextArea(document.getElementById("code-html"), {
							lineNumbers: true,
							matchBrackets: true,
							mode: "mustache",
							indentUnit: 4,
							indentWithTabs: true,
							enterMode: "keep",
							tabMode: "shift",
							lineWrapping: true

						});
						
						CodeMirror.defineMode("jsCode", function(config) {
							return CodeMirror.multiplexingMode(
								CodeMirror.getMode(config, "text/javascript"),
								{open: "<?php echo '<?php';?>", close: "<?php echo '?>';?>",
								 mode: CodeMirror.getMode(config, "text/x-php"),
								 delimStyle: "phptag"}
							);
						});
						CodeMirror.defineMode("jsMustache", function(config, parserConfig) {
							var mustacheOverlay = {
								token: mustache
							};
							return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "jsCode"), mustacheOverlay);
						});            
						var jseditor = CodeMirror.fromTextArea(document.getElementById("code-js"), {
							lineNumbers: true,
							matchBrackets: true,
							mode: "jsMustache",
							indentUnit: 4,
							indentWithTabs: true,
							enterMode: "keep",
							tabMode: "shift",
							lineWrapping: true
						});

						/* Setup autocomplete */
						csseditor.on('keyup', tagFields);
						htmleditor.on('keyup', tagFields);
						jseditor.on('keyup', tagFields);

						
						/* Setup Navigation Tabs */
						// Seyp LOOPER */
						jQuery('#group-set-multiple').on('click', function(e){

								var active = jQuery('.grouptab.active');
								var grouppanel = jQuery(active.attr('href'));
								if(grouppanel.children().length <= 0){return;}
								var tabgroup = grouppanel.children().first().attr('id');
								var clicked = jQuery(this);
								var multis = grouppanel.find('.multivar');
								var multig = grouppanel.find('.multigroup');
								if(clicked.hasClass('button-primary')){
									clicked.removeClass('button-primary');
									multis.val(0);
									multig.val('');
									active.find('.grouptype').addClass('icon-folder-close-alt').removeClass('icon-repeat')
								}else{
									clicked.addClass('button-primary');
									multis.val(1);
									multig.val(tabgroup);
									active.find('.grouptype').removeClass('icon-folder-close-alt').addClass('icon-repeat')
								}

						});
						jQuery('#wpbody-content').on('click', '.navigation-tabs li:not(.fbutton) a', function(e){
								e.preventDefault();
								var alltabs = jQuery('.navigation-tabs li'),
									clicked = jQuery(this),
									previewframe = jQuery('#preview-iframe-holder');

								if(clicked.hasClass('grouptab')){                  
									switchToGroup(clicked);
									return;
								}
								if(clicked.hasClass('attributetab')){
									switchAttVar(clicked);
									return;
								}



									if(clicked.hasClass('left')){
										jQuery('.editor-pane').css({right: 0});
										if(previewframe.is(":visible")){
											previewframe.hide();
											previewframe.addClass('noshow');
										}
									}else{
										if(jQuery('#setShowPreview').val() == 1){
											jQuery('.editor-pane').css({right: '50%'});
											if(!previewframe.is(":visible")){
												previewframe.show();
												previewframe.removeClass('noshow');
											}
										}
										
								}
								alltabs.removeClass('active');
								clicked.parent().addClass('active');
								var panel = jQuery(clicked.attr('href'));
								jQuery('.editor-tab').hide();
								panel.show();
								panel.find('textarea').focus();
								phpeditor.refresh();
								csseditor.refresh();
								htmleditor.refresh();
								jseditor.refresh();
						})

						/* tabbing */
						function switchAttVar(clicked){
									jQuery('.attributetab').removeClass('active');
									jQuery('.confgroup').hide();
									jQuery(clicked.attr('href')).show();
									clicked.addClass('active');
						}
						function switchToGroup(clicked, clean){
							if(clicked.length <= 0){
								jQuery('#var-list').fadeOut(200);
								return;
							}else{
								jQuery('#var-list').fadeIn(200);
							}
							var group = jQuery(clicked.attr('href'));
							var grouplabel = clicked.parent().find('.grouplabel').val();
							jQuery('#group-config').removeClass('button-primary');
							//hide the options panel
							jQuery('.group-options').slideUp(100);
							// quickly set key to first item
							//if()
							var order = [];
							jQuery.each(group.children(), function(){
								order.push(this.id);
							});
							clicked.parent().find('.groupkey').val(order.join(','));
							var groupparent = group.children().first().attr('id');//clicked.parent().find('.groupkey').val();
							if(typeof groupparent === 'undefined'){
								if(jQuery('#var-delete-group').length <= 0){
									jQuery('#var-list .var-tools:first-child').append(deletegroupbuttontemplate);
								}
							}else{
								if(jQuery('#var-delete-group').length >= 1){
									jQuery('#var-delete-group').remove();
								}
							}
							var setMulti = jQuery('#group-set-multiple');
								jQuery('#edit-group-name').val(grouplabel);

							if(typeof clean === 'undefined'){
								jQuery('.grouptab').removeClass('active');
								jQuery('.vargroup').hide();
								jQuery('.confgroup').hide();
								jQuery('.attributetab').removeClass('active');
								group.show();
								clicked.addClass('active');
								setMulti.removeClass('active');
							}

							// clean up and make variables fit
							group.find('.tabgroup').val(grouplabel);
							setMulti.removeClass('button-primary');

							var multis = group.find('.multivar'),
								multig = group.find('.multigroup');
							if(multis.length > 0){
								if(multis.val()[0] > 0){
									if(typeof clean === 'undefined'){
										setMulti.addClass('button-primary');
									}
									multis.val(1);
									multig.val(groupparent);
								}else{
									multis.val();
									multig.val('');
								}
							}

						}            

						/* clean up group settings*/

						function toggleFieldConfigs(){

							jQuery('.typeedit').each(function(k,v){

								var type = jQuery(this).val(),
									parent = jQuery(this).parent().parent(),
									default_config = parent.find('.fieldtype-default'),
									configs = parent.find('.fieldtype-config'),										 
									custom_config = parent.find('.fieldtype-wrap-'+type);

								// hide all configs
								configs.hide();

								if(custom_config.length){
									// show custom
									custom_config.show();
								}else{
									//show default
									default_config.show();
								}
							});
						}
						/* Utility Functions */
						function randomUUID() {
								var s = [], itoh = '0123456789ABCDEF';
								for (var i = 0; i <6; i++) s[i] = Math.floor(Math.random()*0x10);
								return s.join('');
						}

						
						function makeGroupDrops(){
							jQuery( "#groups-list" ).sortable();
							jQuery( ".vargroup" ).sortable({
								update: function(){
									switchToGroup(jQuery('.grouptab.active'));
								}
							});
							jQuery( "#groups-list li a" ).droppable({
								accept: ".vargroup li",

								drop: function(event, ui){
									var $grouppanel = jQuery(jQuery(this).attr('href'));
									var dropped = jQuery(this);
									var parent = ui.draggable.parent();
									ui.draggable.hide(10,function(){
											jQuery(this).appendTo($grouppanel).show(100);
											if(parent.children().length <= 0){
												parent.remove();
												jQuery('.grouptab.active').parent().remove();
												switchToGroup(dropped);
											}else{
												switchToGroup(dropped, true);
											}
									});

								}
							});              
						}

						function update_attributeDefault(el){
							var fields = el.find('.dropdown-options-editor-line');
							var defaultbox = el.find('.defaultedit');
							var vars = [];
							fields.each(function(k,v){
								var line = [];
								var key = jQuery('.dropdown-options-key', v).val().replace('*','');
								var val = jQuery('.dropdown-options-val', v).val().replace('*','');
								var def = jQuery('.isinitial', v).prop('checked');
								if(key.length){
									if(def){
										key = '*'+key;
									}
									line.push(key);
								}
								if(val.length){
									line.push(val);
								}
								if(line.length){
									vars.push(line.join('||'));
								}
							})
							defaultbox.val(vars.join(','));
						}
						/* ready calls */
						jQuery(document).ready(function(){

								jQuery('#zen-toggle').click(function(){ 
									jQuery('html').toggleClass('zen');
									jQuery(this).toggleClass('button-primary');
								});
								jQuery('#preview-toggle').click(brickroad_togglepreview);
								

								//jQuery( "#variablePane" ).sortable();
								makeGroupDrops();
								switchToGroup(jQuery('.grouptab.active'));

								jQuery( "#jslibraryPane" ).sortable();
								jQuery( "#assetPane" ).sortable();
								
								jQuery('#attributes').on('blur', '.new-group-field,.new-var-field', function(e){
									if(this.value.length <= 0){
										jQuery(this).remove();
									}
								});
								jQuery('#attributes').on('change', '.new-group-field', function(e){
									e.preventDefault();
									var id = this.id;
									var group = this.value;

									jQuery(this).remove();
									if(group.length <= 0){return;}

									var grouplist = jQuery('#groups-list');
									var varlist = jQuery('#var-list');
									

									groupline = grouplinetemplate.replace(/{{id}}/g, id).replace(/{{group}}/g, group);
									vargroup = vargrouptemplate.replace(/{{id}}/g, id);
									//var grouptemplate = '<ul id="group'+id+'" class="navigation-tabs vargroup" data-parent="groupentry'+id+'" style="display: none;"></ul>';

									grouplist.append(groupline);
									varlist.append(vargroup);
									makeGroupDrops();
									switchToGroup(jQuery('#groupline'+id));
									toggle_elementConfigs();									
								})

								jQuery('#attributes').on('change', '.new-var-field', function(e){
									e.preventDefault();


									var active = jQuery('.grouptab.active');
									var grouppanel = jQuery(active.attr('href'));
									var group = active.parent().find('.grouplabel').val();
									//var tabgroup = group.children().first().attr('id');
									//if(grouppanel.children().length > 0){
									var id = randomUUID();
									//}else{
									 // var id = group.children().first().attr('id');
									//}
									var slug = this.value.replace(/[^a-z0-9]/gi, '_').toLowerCase();

									jQuery(this).remove();
									if(slug.length <= 0){return;}

									var confpanel = jQuery('#var-config');
									var label = this.value;
									

									//var template = '<li id="'+id+'"><a class="attributetab attributevar active" href="#varconf'+id+'" id="conftab'+id+'"><i class="icon-angle-right"></i> {{'+slug+'}}</a><input type="hidden" name="data[_tabgroup]['+id+']" value="'+group+'" class="tabgroup"><input type="hidden" name="data[_group]['+id+']" value="" class="multigroup"><input type="hidden" name="data[_isMultiple]['+id+']" value="0" class="multivar"></li>';
									//conftemplate = jQuery('#var-config-template').html().replace('{{id}}', id).replace('{{slug}}', slug);
									conf = conftemplate.replace(/{{id}}/g, id).replace(/{{slug}}/g, slug).replace(/{{label}}/g, label);
									varitem = varitemtemplate.replace(/{{id}}/g, id).replace(/{{slug}}/g, slug).replace(/{{group}}/g, group).replace(/{{tabgroup}}/g, '').replace(/{{label}}/g, label);

									grouppanel.append(varitem);
									confpanel.append(conf);
									switchToGroup(active);
									switchAttVar(jQuery('#conftab'+id));
									makeGroupDrops();
									toggleFieldConfigs();
								})

								// edit label config
								jQuery('#attributes').on('keyup','.labeledit', function(){
									var box = jQuery(this);
									jQuery('#label'+box.data('reference')).html(this.value);
									jQuery('#linelabel'+box.data('reference')).html(this.value);
								});
								// edit slug config
								jQuery('#attributes').on('keyup','.slugedit', function(){
									var box = jQuery(this);
									var slug = this.value.replace(/[^a-z0-9]/gi, '_').toLowerCase();
									jQuery('#slug'+box.data('reference')).html('{{'+slug+'}}');
									jQuery('#varitm'+box.data('reference')).html('{{'+slug+'}}');
									box.val(slug);
								});

								//open edit group settings
								jQuery('#attributes').on('click','#group-config', function(){
									var clicked = jQuery(this),
										configpanel = clicked.parent().next(),
										groupitems = clicked.parent().parent().next(),
										activegroup = jQuery('.grouptab.active'),
										label = activegroup.parent().find('.grouplabel');
										
										if(clicked.hasClass('button-primary')){
											configpanel.slideUp(100);
											//groupitems.slideDown(100);
											clicked.removeClass('button-primary')
										}else{
											configpanel.slideDown(100);
											//groupitems.slideUp(100);
											clicked.addClass('button-primary')
										}
								});

								jQuery('#attributes').on('keyup','#edit-group-name', function(){
									if(this.value.length <= 0){return;}
									var pressed = jQuery(this),
										configpanel = pressed.parent().next().find('.tabgroup'),
										activegroup = jQuery('.grouptab.active'),
										label = activegroup.parent().find('.grouplabel').val(pressed.val());
										
										activegroup.find('.grouplabel-text').text(pressed.val());
										configpanel.val(pressed.val());
										//tabgroup
								})

								jQuery('#attributes').on('click','.delete-attribute', function(){
									if(confirm('Are you sure you want to remove this attribute?')){
											var id = jQuery(this).data('reference');
											jQuery('#'+id+',#conftab'+id+',#varconf'+id).remove();

											switchToGroup(jQuery('.grouptab.active'));
									}
								});
								//DELETE GROUP
								jQuery('#attributes').on('click','#delete-group', function(){
									if(!confirm('Are you sure you want to delete this group?')){return;}
									
									var active = jQuery('.grouptab.active');
									jQuery(active.attr('href')).remove();
									active.parent().remove();
									jQuery(this).parent().remove();
									switchToGroup(jQuery('#groups-list li a').first());
									toggle_elementConfigs();

								});

								// ADD group
								jQuery('#add-var-group').click(function(){
									if(jQuery('.new-group-field').length >= 1){
										jQuery('.new-group-field').focus();
										return;
									}
									var grouplist = jQuery('#groups-list');
									var id = randomUUID();
									
									grouplist.append('<input type="text" placeholder="new group" value="" class="new-group-field" id="'+id+'">');
									jQuery('#'+id).focus();
								});

								// ADD Var
								jQuery('#add-group-var').click(function(){
									if(jQuery('.new-var-field').length >= 1){
										jQuery('.new-var-field').focus();
										return;
									}
									var active = jQuery('.grouptab.active');
									var grouppanel = jQuery(active.attr('href'));
									var group = active.parent().find('.grouplabel');
									var id = randomUUID();

									grouppanel.append('<input type="text" placeholder="new attribute" class="new-var-field" id="newvar'+id+'">');
									jQuery('#newvar'+id).focus();
								});



								// ADD Options Line
								jQuery('.editor-tab-content').on('click','.dropdown-options-add-line',function(){
									var optionsList = jQuery(this).parent();
									var optionLine = optionsLineTemplate;
									update_attributeDefault(optionsList.parent());
									optionsList.append(optionLine);
								});

								/// BIND CHANGED TO DEFAULTS
								jQuery('.editor-tab-content').on('change','.dropdown-options-editor',function(){
									var thisbox = jQuery(this);
									thisbox.val(thisbox.val().replace('||',' ').replace(',',''));
									update_attributeDefault(thisbox.parent().parent().parent());
								});

								// bind change slider								
								jQuery('.editor-tab-content').on('keyup','.slider-conf-val',function(){

									var parent 		= jQuery(this).parent().parent().parent(),
										min			= parent.find('.slider-min-val').val(),
										max			= parent.find('.slider-max-val').val(),
										def			= parent.find('.slider-default-val').val(),
										suf			= parent.find('.slider-suffix-val').val(),
										defaultval	= parent.find('.defaultedit'),
										newval		= '';
									
									newval = min + ',' + max + '|'+ def;
									if(suf.length > 0){
										newval += '|' + suf;
									}

									defaultval.val(newval);

								});

								/// BIND CHANGED TO DEFAULTS
								jQuery('.editor-tab-content').on('change','.post-type-select',function(){

									var parent 		= jQuery(this).parent().parent().parent(),
										typesel		= parent.find('.post-type-selected-type').val(),
										defid 		= parent.find('.post-type-default-id').val(),
										defaultval	= parent.find('.defaultedit');
									
									if(defid.length){
										defaultval.val(typesel + '||' + defid );
									}else{
										defaultval.val(typesel);
									}
								});

								/// BIND CHANGED TO DEFAULTS
								jQuery('.editor-tab-content').on('change','.field-type-select',function(){

									var parent 		= jQuery(this).parent().parent().parent(),
										typesel		= parent.find('.field-type-selected-type').val(),
										defaultval	= parent.find('.defaultedit');
									

										defaultval.val(typesel);
								});

								/// BIND CHANGE TO TYPES
								jQuery('.editor-tab-content').on('change','.typeedit',function(){

									toggleFieldConfigs();

								});
								///	
								jQuery('.editor-tab-content').on('change','.isinitial', function(){
									var curr = jQuery(this);
									var prop = curr.prop('checked');
									var confpanel = curr.parent().parent().parent();
									confpanel.find('.isinitial').removeProp('checked');
									if(prop){
										curr.prop('checked','checked');
									}
									update_attributeDefault(confpanel);
								});

								/// Remove Option Ediror Line
								jQuery('.editor-tab-content').on('click','.remove-option-line', function(){
									var button = jQuery(this);
									var confpanel = button.parent().parent().parent();
									button.parent().remove();
									update_attributeDefault(confpanel);
								});
						});
/* Dashicons Picker */

(function($) {

	$.fn.dashiconsPicker = function( options ) {
		var icons = [
			"menu",
			"admin-site",
			"dashboard",
			"admin-media",
			"admin-page",
			"admin-comments",
			"admin-appearance",
			"admin-plugins",
			"admin-users",
			"admin-tools",
			"admin-settings",
			"admin-network",
			"admin-generic",
			"admin-home",
			"admin-collapse",
			"admin-links",
			"format-links",
			"admin-post",
			"format-standard",
			"format-image",
			"format-gallery",
			"format-audio",
			"format-video",
			"format-chat",
			"format-status",
			"format-aside",
			"format-quote",
			"welcome-write-blog",
			"welcome-edit-page",
			"welcome-add-page",
			"welcome-view-site",
			"welcome-widgets-menus",
			"welcome-comments",
			"welcome-learn-more",
			"image-crop",
			"image-rotate-left",
			"image-rotate-right",
			"image-flip-vertical",
			"image-flip-horizontal",
			"undo",
			"redo",
			"editor-bold",
			"editor-italic",
			"editor-ul",
			"editor-ol",
			"editor-quote",
			"editor-alignleft",
			"editor-aligncenter",
			"editor-alignright",
			"editor-insertmore",
			"editor-spellcheck",
			"editor-distractionfree",
			"editor-kitchensink",
			"editor-underline",
			"editor-justify",
			"editor-textcolor",
			"editor-paste-word",
			"editor-paste-text",
			"editor-removeformatting",
			"editor-video",
			"editor-customchar",
			"editor-outdent",
			"editor-indent",
			"editor-help",
			"editor-strikethrough",
			"editor-unlink",
			"editor-rtl",
			"align-left",
			"align-right",
			"align-center",
			"align-none",
			"lock",
			"calendar",
			"visibility",
			"post-status",
			"post-trash",
			"edit",
			"trash",
			"arrow-up",
			"arrow-down",
			"arrow-left",
			"arrow-right",
			"arrow-up-alt",
			"arrow-down-alt",
			"arrow-left-alt",
			"arrow-right-alt",
			"arrow-up-alt2",
			"arrow-down-alt2",
			"arrow-left-alt2",
			"arrow-right-alt2",
			"leftright",
			"sort",
			"list-view",
			"exerpt-view",
			"share",
			"share1",
			"share-alt",
			"share-alt2",
			"twitter",
			"rss",
			"facebook",
			"facebook-alt",
			"networking",
			"googleplus",
			"hammer",
			"art",
			"migrate",
			"performance",
			"wordpress",
			"wordpress-alt",
			"pressthis",
			"update",
			"screenoptions",
			"info",
			"cart",
			"feedback",
			"cloud",
			"translation",
			"tag",
			"category",
			"yes",
			"no",
			"no-alt",
			"plus",
			"minus",
			"dismiss",
			"marker",
			"star-filled",
			"star-half",
			"star-empty",
			"flag",
			"location",
			"location-alt",
			"camera",
			"images-alt",
			"images-alt2",
			"video-alt",
			"video-alt2",
			"video-alt3",
			"vault",
			"shield",
			"shield-alt",
			"search",
			"slides",
			"analytics",
			"chart-pie",
			"chart-bar",
			"chart-line",
			"chart-area",
			"groups",
			"businessman",
			"id",
			"id-alt",
			"products",
			"awards",
			"forms",
			"portfolio",
			"book",
			"book-alt",
			"download",
			"upload",
			"backup",
			"lightbulb",
			"smiley"
		];

		return this.each( function() {

			var $button = $(this);

			$button.on('click.dashiconsPicker', function() {
				createPopup($button);
			});

			function createPopup($button) {

				$target = $($button.data('target'));
				$container = ( $button.data('container') ? $($button.data('container')) : 'body' );

				$popup = $('<div class="dashicon-picker-container"> \
						<div class="dashicon-picker-control" /> \
						<ul class="dashicon-picker-list" /> \
					</div>')
					.css({
						'top': $button.offset().top,
						'left': $button.offset().left
					});

				var $list = $popup.find('.dashicon-picker-list');
				for (var i in icons) {
					$list.append('<li data-icon="'+icons[i]+'"><a href="#" title="'+icons[i]+'"><span class="dashicons dashicons-'+icons[i]+'"></span></a></li>');
				};

				$('a', $list).click(function(e) {
					e.preventDefault();
					var title = $(this).attr("title");
					$target.val("dashicons-"+title);
					removePopup();
				});

				var $control = $popup.find('.dashicon-picker-control');
				$control.html('<a data-direction="back" href="#"><span class="dashicons dashicons-arrow-left-alt2"></span></a> \
				<input type="text" class="" placeholder="Search" /> \
				<a data-direction="forward" href="#"><span class="dashicons dashicons-arrow-right-alt2"></span></a>');

				$('a', $control).click(function(e) {
					e.preventDefault();
					if ($(this).data('direction') === 'back') {
						//move last 25 elements to front
						$('li:gt(' + (icons.length - 26) + ')', $list).each(function() {
							$(this).prependTo($list);
						});
					} else {
						//move first 25 elements to the end
						$('li:lt(25)', $list).each(function() {
							$(this).appendTo($list);
						});
					}
				});

				$popup.appendTo($container).show();

				$('input', $control).on('keyup', function(e) {
					var search = $(this).val();
					if (search === '') {
						//show all again
						$('li:lt(25)', $list).show();
					} else {
						$('li', $list).each(function() {
							if ($(this).data('icon').toLowerCase().indexOf(search.toLowerCase()) !== -1) {
								$(this).show();
							} else {
								$(this).hide();
							}
						});
					}
				});

				$(document).mouseup(function (e){
					if (!$popup.is(e.target) && $popup.has(e.target).length === 0) {
						removePopup();
					}
				});
			}

			function removePopup(){
				$(".dashicon-picker-container").remove();
			}
		});
	}

	$(function() {
		$('.dashicons-picker').dashiconsPicker();
	});

}(jQuery));