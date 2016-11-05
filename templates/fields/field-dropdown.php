						<?php

							$structure = explode(',',$settings['default']);
							$defaultSelect = null;
							$options = array();
							foreach($structure as &$option){
								if(false !== strpos($option, '||') ){
									$parts = explode('||', $option);
									$options[$parts[0]] = $parts[1];
									if(false !== stripos($option, '*')){
										$defaultSelect = $parts[0];
									}									
								}else{
									$options[$option] = ucwords($option);
									if(false !== stripos($option, '*')){
										$defaultSelect = $option;
									}									
								}
							}
							echo "<select name=\"".$name."\" id=\"".$id."\" >\r\n"; 
							if($settings['default'] === $value && !empty($defaultSelect)){
								$value = $defaultSelect;
							}else if(empty($defaultSelect)){ ?>

								<option name="<?php echo $name; ?>" <?php if($value == $settings['default'] || strlen($value) === 0){ echo 'selected="selected"'; }; ?> value=""></option> 
							<?php }

							$dropdownindex = 0;
							foreach($options as $dropdownValue=>$dropdownLabel){
								$dropdownValue = str_replace('*', '', $dropdownValue);
								?>
								<option <?php if($value == $dropdownValue){ echo 'selected="selected"'; }; ?> value="<?php echo $dropdownValue; ?>"> <?php echo str_replace('*', '', $dropdownLabel); ?></option>
							<?php } ?>
							</select>
