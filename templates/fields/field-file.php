				<?php
					$text = '<span class="noselection {{key}}_uploader button">'.__('Select File','{{slug}}').'</span>';
						$bg = '';
						
						if(!empty($value)){
							if(floatval($value) > 0){

								$text = wp_get_attachment_image($value, 'thumbnail', true, array('class'=>'filepreview image')).' '.basename(wp_get_attachment_url($value)).' <span class="filechanger-btn {{key}}_uploader button">Change File</span> <span class="button removefile">&times;</span>';
							}else{
								$text = basename($value).' <span class="filechanger-btn {{key}}_uploader button">'.__('Change File','{{slug}}').'</span> <span class="button removefile">&times;</span>';
							}
						}
						?>
					<input name="<?php echo $name; ?>" type="hidden" class="regular-text" class="imageid" ref="<?php echo $id; ?>" id="<?php echo $id; ?>_id" value="<?php echo $value; ?>" />
					<div id="uploader_<?php echo $id; ?>" data-field="<?php echo $id; ?>" style="float:none;">
						<div class="file-field-{{slug}} filepreview" '.$bg.'><?php echo $text; ?></div>
					</div>