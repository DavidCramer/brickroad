<?php

$editorArgs = array(
	'media_buttons' => false,
	'textarea_name'	=> $name
);

wp_editor( $value, $id, $editorArgs );