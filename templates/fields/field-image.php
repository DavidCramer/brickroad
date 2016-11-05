				<?php
					$text = '<span class="noselection {{key}}_media_select button">'.__('Select Image','{{slug}}').'</span>';
					$bg = '';
					if(!empty($value)){
						$attachment = explode(',',$value);
						if(floatval($attachment[0]) > 0){
							$sizes = get_intermediate_image_sizes();
							$previewimage = false;
							$sizebuttons = '';
							$setsize = '';
							$imageurl = "";
							$sizes[] = 'full';
							foreach ($sizes as $avsize) {
								$image = wp_get_attachment_image_src($attachment[0], $avsize);
								if(!empty($image[3]) || $avsize == 'full'){
									$sel = '';
									if($avsize == $attachment[1]){
										$sel = 'selected';
										$setsize = '<span class="preview">'.$image[1].'x'.$image[2].'</span>';
									}
									$sizebuttons .= '<span class="sizes '.$avsize.' '.$sel.'" data-size="'.$image[1].'x'.$image[2].'">'.$avsize.'</span>';
									if($avsize == 'medium'){
										$previewimage = $image;
									}
									$backuppreview = $image;
								}
							}
							if(empty($previewimage)){
								$previewimage = $backuppreview;
							}
							if($previewimage[1] >= $previewimage[2]){
								$repons = '100% auto';	
							}else{
								$repons = 'auto 100%';
							}
							$text = '<span class="remove">&times;</span>'.$setsize.$sizebuttons.'<span class="filechanger-btn {{key}}_media_select changer">'.__('Change Image','{{slug}}').'</span>';
							$bg = 'style="background: url('.$previewimage[0].') no-repeat scroll center center / '.$repons.' #EFEFEF;"';
						}else{
							$sizes = getimagesize($value);
							$percdif = $sizes[0]/300*100;
							$newh = $sizes[1]*$percdif/100+$sizes[1];
							if($newh > 120){
								$repons = '100% auto';	
							}else{
								$repons = 'auto 100%';
							}
							$text = '<span class="remove">&times;</span><span class="filechanger-btn {{key}}_media_select changer">'.__('Change Image','{{slug}}').'</span><span class="preview">'.$sizes[0].'x'.$sizes[1].'</span>';
							$bg = 'style="background: url('.$value.') no-repeat scroll center center / '.$repons.' #EFEFEF;"';
						}
					}
					echo '<div id="uploader_'.$id.'" data-field="'.$id.'" style="float:none;">';
					echo '<input name="'.$name.'" type="hidden" class="regular-text imageid" ref="'.$id.'" value="'.$value.'" />';
					echo '	<div class="image-field-{{slug}} imgpreview" '.$bg.'>'.$text.'</div>';
					echo '</div>';
					?>