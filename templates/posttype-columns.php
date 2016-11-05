<?php
	
	/**
	 * Custom Post type columns.
	 *
	 *
	 * @return    columns
	 */
	function posts_column($hd){
		unset($hd['date']);
		$hd['shortcode_slug'] = __('Shortcode', '{{slug}}');
		$hd['date'] = __('Date', '{{slug}}');
		return $hd;
	}
	
	/**
	 * Custom Post type column render.
	 *
	 *
	 */
	function custom_postcolumn($col, $id){
		echo '['.$_GET['post_type'].' id="'.$id.'"]';
	}
