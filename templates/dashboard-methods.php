<?php
	
	/**
	 * Add a widgets to the dashboard.
	 *
	 */
	public function add_dashboard_widgets() {

		wp_add_dashboard_widget(
	                 'slug',         // Widget slug.
	                 'title',         // Title.
	                 array($this, 'method') // Display function.
	        );	
	}
	//add_action( 'wp_dashboard_setup', 'example_add_dashboard_widgets' );

	/**
	 * Render the Dashboard Widget.
	 */
	public function dashboard_widget_render() {

	} 