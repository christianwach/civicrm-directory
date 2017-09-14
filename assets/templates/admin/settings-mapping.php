<!-- assets/templates/admin/settings-mapping.php -->
<div id="icon-options-general" class="icon32"></div>

<div class="wrap">

	<h1><?php _e( 'CiviCRM Directory Settings', 'civicrm-directory' ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo $urls['general']; ?>" class="nav-tab"><?php _e( 'General', 'civicrm-directory' ); ?></a>
		<a href="<?php echo $urls['mapping']; ?>" class="nav-tab nav-tab-active"><?php _e( 'Mapping', 'civicrm-directory' ); ?></a>
	</h2>

	<?php if ( isset( $messages ) AND ! empty( $messages ) ) echo $messages; ?>

	<form method="post" id="civicrm_directory_settings_mapping_form" action="<?php echo $urls['mapping']; ?>">

		<?php wp_nonce_field( 'civicrm_directory_settings_mapping_action', 'civicrm_directory_nonce' ); ?>

		<h2><?php _e( 'Mapping Defaults', 'civicrm-directory' ); ?></h2>

		<p><?php _e( 'Set defaults for Google Maps', 'civicrm-directory' ); ?></p>

		<table class="form-table">

			<tr>
				<th scope="row"><label class="civicrm_directory_settings_label" for="civicrm_directory_google_maps_key"><?php _e( 'Google Maps API Key', 'civicrm-directory' ); ?></label></th>
				<td>
					<input type="text" class="widefat" id="civicrm_directory_google_maps_key" name="civicrm_directory_google_maps_key" value="<?php esc_attr_e( $google_maps_key ); ?>" />
				</td>
			</tr>

			<tr>
				<th scope="row"><label class="civicrm_directory_settings_label" for="civicrm_directory_latitude"><?php _e( 'Default Latitude', 'civicrm-directory' ); ?></label></th>
				<td>
					<input type="text" id="civicrm_directory_latitude" name="civicrm_directory_latitude" value="<?php esc_attr_e( $latitude ); ?>" />
				</td>
			</tr>

			<tr>
				<th scope="row"><label class="civicrm_directory_settings_label" for="civicrm_directory_longitude"><?php _e( 'Default Longitude', 'civicrm-directory' ); ?></label></th>
				<td>
					<input type="text" id="civicrm_directory_longitude" name="civicrm_directory_longitude" value="<?php esc_attr_e( $longitude ); ?>" />
				</td>
			</tr>

			<tr>
				<th scope="row"><label class="civicrm_directory_settings_label" for="civicrm_directory_zoom"><?php _e( 'Default Zoom Level', 'civicrm-directory' ); ?></label></th>
				<td>
					<input type="text" id="civicrm_directory_zoom" name="civicrm_directory_zoom" value="<?php esc_attr_e( $zoom ); ?>" />
				</td>
			</tr>

		</table>

		<hr>

		<p class="submit">
			<input class="button-primary" type="submit" id="civicrm_directory_settings_mapping_submit" name="civicrm_directory_settings_mapping_submit" value="<?php _e( 'Save Changes', 'civicrm-directory' ); ?>" />
		</p>

	</form>

</div><!-- /.wrap -->



