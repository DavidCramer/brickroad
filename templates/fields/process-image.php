<?php
			case "image":
				$attachment = explode(',',$value);
				if(floatval($attachment[0]) > 0){
					$image = wp_get_attachment_image_src($attachment[0], $attachment[1]);
					$value = $image[0];
				}
			break;