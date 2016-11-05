<div style="padding: 3px 0 10px;">
    <button type="button" onclick="brickroad_addAsset();" class="button"><i class="icon-plus-sign"></i> Add Asset</button>
</div>
<div id="assetPane">
    <?php
    if(!empty($Element['_assetLabel'])){
     foreach($Element['_assetLabel'] as $assetKey=>$Label){
        echo '<div id="'.$assetKey.'" class="attributeItem assetItem" data-field="'.$assetKey.'">';
            echo '<input type="hidden" value="'.$Element['_assetURL'][$assetKey].'" name="data[_assetURL]['.$assetKey.']" class="fileURL" id="'.$assetKey.'_id">';
            echo '<div style="float:left;">';
                echo '<label for="lable_'.$assetKey.'">Slug: </label>';
                echo '<input type="text" value="'.$Label.'" name="data[_assetLabel]['.$assetKey.']" style="width:70px;margin-right:20px" id="lable_'.$assetKey.'" class="assetlabel">';
            echo '</div>';            
            echo '<div>';
            if(floatval($Element['_assetURL'][$assetKey]) > 0){
                echo wp_get_attachment_image($Element['_assetURL'][$assetKey], 'thumbnail', true, array('class'=>'filepreview image')).' '.basename(wp_get_attachment_url($Element['_assetURL'][$assetKey])).' <span class="filechanger-btn brickroad_uploader button">Change File</span> <span class="button removefile">&times;</span>';
            }else{
                echo  basename($Element['_assetURL'][$assetKey]).'<span class="filechanger-btn brickroad_uploader button">Change File</span> <span class="button removefile">&times;</span>';
            }
            echo '</div>';
        echo '</div>';

     }
    }
    ?>
</div>
<script type="text/javascript">
    jQuery('#tabid8').click(function(){
        jQuery('#editorPane .tabs a').removeClass('active');
        jQuery(this).addClass('active');
        jQuery('#editorPane .area article').hide();
        jQuery(jQuery(this).attr('href')).show();
    });

    function brickroad_addAsset(){
        var rowID = randomUUID();
        jQuery('#assetPane').append('<div class="attributeItem assetItem" id="'+rowID+'" data-field="'+rowID+'"><input id="'+rowID+'_id" type="hidden" name="data[_assetURL]['+rowID+']" value="" /><div style="float:left;"><label for="lable_'+rowID+'">Slug: </label><input type="text" id="lable_'+rowID+'" style="width:70px;margin-right:20px" class="assetlabel" name="data[_assetLabel]['+rowID+']" value="" /></div><div><input class="button brickroad_uploader" id="button_'+rowID+'" type="button" value="Select File" /> <span class="button removefile">×</span></div>');
    }
    jQuery(document).ready(function() {
        var _custom_media = true,
        _orig_send_attachment = wp.media.editor.send.attachment;

        jQuery('body').on('click','.brickroad_uploader', function() {
            brickroad_open_filemodal(this);
        });
        jQuery('body').on('click','.brickroad_image_uploader', function() {
            brickroad_open_imagemodal(this);
        });

        jQuery('body').on('click', '.removefile', function(){
            jQuery(this).parent().parent().remove();
        })

    });

</script>