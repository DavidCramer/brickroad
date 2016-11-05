
jQuery(function($){
	var selection = false;
	var {{key}}ShortcodePanel = $('#{{slug}}-shortcode-panel-tmpl').html();

	$('body').append({{key}}ShortcodePanel);
	$('.media-modal-backdrop, .media-modal-close').on('click', function(){
		{{key}}_hideModal();
	})
	$(document).keyup(function(e) {
		if (e.keyCode == 27) {
			{{key}}_hideModal();
		}
	});

	// show modal
	$(document).on('click', '#{{slug}}-shortcodeinsert', function(){
		if($(this).data('shortcode')){
			window.send_to_editor('['+$(this).data('shortcode')+']');
			return;
		}
				
		// autoload item
		var autoload = $('.{{slug}}-autoload');
		if(autoload.length){
			{{key}}_loadtemplate(autoload.data('shortcode'));
		}
		$('#{{slug}}-category-selector').on('change', function(){
			{{key}}_loadtemplate('');
			$('.{{slug}}-elements-selector').hide();
			$('#{{slug}}-elements-selector-'+this.value).show().val('');
		});

		$('.{{slug}}-elements-selector').on('change', function(){
			{{key}}_loadtemplate(this.value);
		});

		if(typeof tinyMCE !== 'undefined'){
			if(tinyMCE.activeEditor !== null){
				selection = tinyMCE.activeEditor.selection.getContent();
			}else{
				selection = false;
			}
		}else{
			selection = false;
		}
		if(selection.length > 0){
			$('#{{slug}}-content').html(selection);
		}
		$('#{{slug}}-shortcode-panel').show();
	});
	$('#{{slug}}-insert-shortcode').on('click', function(){
		{{key}}_sendCode();
	})
	// modal tabs
	$('#{{slug}}-shortcode-config').on('click', '.{{slug}}-shortcode-config-nav li a', function(){
		$('.{{slug}}-shortcode-config-nav li').removeClass('current');
		$('.group').hide();
		$(''+$(this).attr('href')+'').show();
		$(this).parent().addClass('current');
		return false;
	});


});

function {{key}}_loadtemplate(shortcode){
	var target = jQuery('#{{slug}}-shortcode-config');
	if(shortcode.length <= 0){
		target.html('');
	}
	target.html(jQuery('#{{slug}}-'+shortcode+'-config-tmpl').html());
}

function {{key}}_sendCode(){

	var shortcode = jQuery('#{{slug}}-shortcodekey').val(),
		output = '['+shortcode,
		ctype = '',
		fields = {};
	
	if(shortcode.length <= 0){return; }

	if(jQuery('#{{slug}}-shortcodetype').val() === '2'){
		ctype = jQuery('#{{slug}}-default-content').val()+'[/'+shortcode+']';
	}
	jQuery('#{{slug}}-shortcode-config input,#{{slug}}-shortcode-config select,#{{slug}}-shortcode-config textarea').not('.configexclude').each(function(){
		if(this.value){
			// see if its a checkbox
			var thisinput = jQuery(this),
				attname = this.name;

			if(thisinput.prop('type') == 'checkbox'){
				if(!thisinput.prop('checked')){
					return;
				}
			}
			if(thisinput.prop('type') == 'radio'){
				if(!thisinput.prop('checked')){
					return;
				}
			}

			if(attname.indexOf('[') > -1){
				attname = attname.split('[')[0];
				var newloop = {};
				newloop[attname] = this.value;
				if(!fields[attname]){
					fields[attname] = [];
				}
				fields[attname].push(newloop);
			}else{
				var newfield = {};
				fields[attname] = this.value;
			}
		}
	});
	for( var field in fields){
		if(typeof fields[field] == 'object'){
			for(i=0;i<fields[field].length; i++){
				output += ' '+field+'_'+(i+1)+'="'+fields[field][i][field]+'"';
			}
		}else{
			output += ' '+field+'="'+fields[field]+'"';
		}
	}
	{{key}}_hideModal();
	window.send_to_editor(output+']'+ctype);

}
function {{key}}_hideModal(){
	jQuery('#{{slug}}-shortcode-panel').hide();
	{{key}}_loadtemplate('');
	jQuery('#{{slug}}-elements-selector').show();
	jQuery('.{{slug}}-elements-selector').val('');	
	jQuery('#{{slug}}-category-selector').val('');
}
