						<?php
							if(!empty($settings['default'])){
								$structure = explode(',',$settings['default']);
								$options = array();
								foreach ($structure as $key => $part) {
									$option = explode('||', $part);
									if(isset($option[1])){
										if(false !== strpos($option[0], '*')){
											if(empty($instance)){
												$value = array(
													$key => str_replace('*', '', $option[0])
												);
											}
										}
										$options[$option[0]] = $option[1];
									}else{
										$options[$option[0]] = $option[0];
									}
								}
							}else{
								$options = array('true'=>'True');
							}
							
							$value = (array) $value;
							$checkboxindex = 0;

							foreach($options as $checkboxValue=>$checkboxLabel){
								$checkboxValue = str_replace('*', '', $checkboxValue);
								$sel = null;
								if(isset($value[$checkboxindex])){
									if(empty($settings['default']) && empty($value[$checkboxindex])){

									}
									if($checkboxValue == $value[$checkboxindex]){
										$sel = 'checked="checked"';
									};
								}
							?>
								<p><label style="margin-left: 8px;"><input type="checkbox" name="<?php echo $name; ?>[<?php echo $checkboxindex; ?>]" <?php echo $sel; ?> id="<?php echo $id.'_'.$checkboxindex; ?>" value="<?php echo $checkboxValue; ?>"> <?php echo $checkboxLabel; ?></label></p>
							<?php 
								$checkboxindex++;
							} ?>
