<!-- assets/templates/admin/settings-general.php -->
<div id="icon-options-general" class="icon32"></div>

<div class="wrap">

	<h1><?php _e( 'CiviCRM Directory Settings', 'civicrm-directory' ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo $urls['general']; ?>" class="nav-tab nav-tab-active"><?php _e( 'General', 'civicrm-directory' ); ?></a>
		<a href="<?php echo $urls['mapping']; ?>" class="nav-tab"><?php _e( 'Mapping', 'civicrm-directory' ); ?></a>
	</h2>

	<?php if ( isset( $messages ) AND ! empty( $messages ) ) echo $messages; ?>

	<form method="post" id="civicrm_directory_settings_general_form" action="<?php echo $urls['general']; ?>">

		<?php wp_nonce_field( 'civicrm_directory_settings_general_action', 'civicrm_directory_nonce' ); ?>

		<h2><?php _e( 'General Settings', 'civicrm-directory' ); ?></h2>

		<table class="form-table">

			<tr>
				<th scope="row"><label class="civicrm_directory_settings_label" for="civicrm_directory_civicrm_group_id"><?php _e( 'Default CiviCRM Group', 'civicrm-directory' ); ?></label></th>
				<td>
					<select name="civicrm_directory_civicrm_group_id" id="civicrm_directory_civicrm_group_id">
						<option value=""<?php if ( empty( $group_id ) ) { echo ' selected="selected"'; } ?>>- <?php _e( 'Select a Group', 'civicrm-directory' ) ?> -</option>
						<?php foreach( $groups AS $key => $data ) { ?>
							<option value="<?php esc_attr_e( $key ); ?>"<?php if ( $key == $group_id ) { echo ' selected="selected"'; } ?>><?php esc_html_e( $data['title'] ); ?></option>
						<?php } ?>
					</select>
					<p><?php _e( 'Choose the default CiviCRM Group to which Directory Contacts belong. This can be overridden on individual Directories.', 'civicrm-directory' ); ?></p>
				</td>
			</tr>

		</table>

		<hr>

		<p class="submit">
			<input class="button-primary" type="submit" id="civicrm_directory_settings_general_submit" name="civicrm_directory_settings_general_submit" value="<?php _e( 'Save Changes', 'civicrm-directory' ); ?>" />
		</p>

	</form>

</div><!-- /.wrap -->



