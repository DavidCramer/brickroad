						<?php

							$structure = explode('||',$settings['default']);
							//dump($structure,0);
							
							
							if(!empty($structure[1]) && empty($value)){
								$value = $structure[1];
							}
							if($structure[0] == 'page'){
								$args = array(
									'depth'		=> 0,
									'child_of'	=> 0,
									'selected'	=> $value,
									'echo'		=> 1,
									'id'		=> $id,
									'name'		=> $name
								);
								wp_dropdown_pages( $args );
								return;
							}

							$posts = get_posts(
							    array(
							        'post_type'  => $structure[0],
							        'numberposts' => -1
							    )
							);

							echo "<select name=\"".$name."\" id=\"".$id."\" >\r\n"; 
							echo '<option value=""></option>';
							foreach( $posts as $p ){
								$sel = '';
								if($p->ID == $value){
									$sel = 'selected="selected"';
								}
							    echo '<option value="' . $p->ID . '" '.$sel.'>' . esc_html( $p->post_title ) . '</option>';
							}
							echo '</select>';
