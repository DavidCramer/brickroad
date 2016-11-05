// File Modal window

function {{key}}_open_filemodal(el){
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
		preview.html(icon+objSettings.filename+' <span class="filechanger-btn {{key}}_uploader button">Change File</span> <span class="button removefile">&times;</span>');

	});

	// Open ML
	frame.open();
}

jQuery(function($){
	// media modals -- maybe its own file!
	if(typeof wp !== 'undefined'){
		if(typeof wp.media === 'undefined'){return;}
		var _custom_media = true,
		_orig_send_attachment = wp.media.editor.send.attachment;
		
		$('body').on('click','.file-field-{{slug}} .{{key}}_uploader', function() {
			{{key}}_open_filemodal(this);
		});
		$('body').on('click','.file-field-{{slug}} .removefile', function() {
			var box = $(this).parent(),
			field = $('#'+box.parent().data('field')+'_id').val('');
			////console.log(box);
			box.html('<span class="noselection {{key}}_uploader button">Select File</span>').removeAttr('style');
		});
	}
})

