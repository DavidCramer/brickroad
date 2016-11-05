<?php
	
	  $messages['{{_shortcode}}'] = array(
	    0 => '', // Unused. Messages start at index 1.
	    1 => sprintf( __('{{_singleName}} updated.{{_viewLinkInline}}', '{{slug}}'), esc_url( get_permalink($post_ID) ) ),
	    2 => __('Custom field updated.', '{{slug}}'),
	    3 => __('Custom field deleted.', '{{slug}}'),
	    4 => __('{{_singleName}} updated.', '{{slug}}'),
	    /* translators: %s: date and time of the revision */
	    5 => isset($_GET['revision']) ? sprintf( __('{{_singleName}} restored to revision from %s', '{{slug}}'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	    6 => sprintf( __('{{_singleName}} published.{{_viewLinkInline}}', '{{slug}}'), esc_url( get_permalink($post_ID) ) ),
	    7 => __('{{_singleName}} saved.', '{{slug}}'),
	    8 => sprintf( __('{{_singleName}} submitted.{{_viewLinkBlank}}', '{{slug}}'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	    9 => sprintf( __('{{_singleName}} scheduled for: <strong>%1$s</strong>.{{_viewLinkBlankScheduled}}', '{{slug}}'),
	      // translators: Publish box date format, see http://php.net/date
	      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	    10 => sprintf( __('{{_singleName}} draft updated.{{_viewLinkBlank}}', '{{slug}}'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	  );
