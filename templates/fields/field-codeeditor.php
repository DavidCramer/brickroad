						<?php
							//value set type & default
							$default = explode('|',$settings['default'], 2);
							$mode = "text/html";
							if(isset($default[1])){
								$mode = $default[0];
								$default = $default[1];
							}else{
								$default = $default[0];
							}
							if($value == $settings['default']){
								$value = $default;
							}

						?>
						<textarea name="<?php echo $name; ?>" data-mode="<?php echo $mode; ?>" class="widefat {{key}}_codemirror_code_editor" cols="20" rows="8" ref="<?php echo $groupid; ?>" id="<?php echo $id; ?>"><?php echo htmlentities( $value ); ?></textarea>