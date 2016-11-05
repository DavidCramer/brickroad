<?php
			case "file":
				if(floatval($value) > 0){
					$value = wp_get_attachment_url($value);
				}
				break;