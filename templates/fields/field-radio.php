						<?php
							
							if(!empty($settings['default'])){
								$structure = explode(',',$settings['default']);
								$options = array();
								if(false === strpos($settings['default'], '*')){
									$options['null'] =  'No Selection';
									if($value == $settings['default']){
										$value = 'null';
									}
								}
								foreach ($structure as $key => $part) {
									$option = explode('||', $part);
									if(isset($option[1])){
										if(false !== strpos($option[0], '*')){
											if($value == $settings['default']){
												$value = str_replace('*', '', $option[0]);
											}
										}
										$options[$option[0]] = $option[1];
									}else{
										$options[$option[0]] = $option[0];
									}
								}
							}else{
								$options = array('true'=>'True', 'false'=>'False');
							}
							
							$checkboxindex = 0;
							// holder to state a blank
							foreach($options as $checkValue=>$checkboxLabel){
								$checkValue = str_replace('*', '', $checkValue);
								$sel = null;
								if($checkValue == $value){
									$sel = 'checked="checked"';
								};
							?>
								<p><label style="margin-left: 8px;"><input type="radio" name="<?php echo $name; ?>" <?php echo $sel; ?> id="<?php echo $id.'_'.$checkboxindex; ?>" value="<?php echo $checkValue; ?>"> <?php echo $checkboxLabel; ?></label></p>
							<?php 
								$checkboxindex++;
							} ?>
