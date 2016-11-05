		<div class="wrap">
			<form method="post" action="options.php" class="{{slug}}-options-form">
            <h2><?php echo __( '{{_pageTitle}}', '{{slug}}'); ?></h2>
            <?php
                if( !empty( $_GET['settings-updated'] ) && $screen->parent_base != 'options-general' ){
                    echo '<div class="updated settings-error" id="setting-error-settings_updated">';
                    echo '<p><strong>' . __('Settings saved.', '{{slug}}') . '</strong></p></div>';
                }
            ?>            
            {{description}}
            <?php
			// hidden fields
			settings_fields( $base );
			do_settings_sections( $base );
			$do_structures = true;