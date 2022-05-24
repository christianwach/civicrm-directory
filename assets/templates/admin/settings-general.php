<?php
/**
 * General Settings Template.
 *
 * Handles General Settings markup.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?><!-- assets/templates/admin/settings-general.php -->
<div id="icon-options-general" class="icon32"></div>

<div class="wrap">

	<h1><?php esc_html_e( 'CiviCRM Directory Settings', 'civicrm-directory' ); ?></h1>

	<?php
	if ( isset( $messages ) && ! empty( $messages ) ) {
		echo $messages;
	}
	?>

	<form method="post" id="civicrm_directory_settings_form" action="<?php echo $url; ?>">

		<?php wp_nonce_field( 'civicrm_directory_settings_action', 'civicrm_directory_nonce' ); ?>

		<hr>

		<h2><?php esc_html_e( 'Mapping Settings', 'civicrm-directory' ); ?></h2>

		<p><?php esc_html_e( 'Set defaults for Google Maps', 'civicrm-directory' ); ?></p>

		<table class="form-table">

			<tr>
				<th scope="row"><label class="civicrm_directory_settings_label" for="civicrm_directory_google_maps_key"><?php esc_html_e( 'Google Maps API Key', 'civicrm-directory' ); ?></label></th>
				<td>
					<input type="text" class="widefat" id="civicrm_directory_google_maps_key" name="civicrm_directory_google_maps_key" value="<?php echo esc_attr( $google_maps_key ); ?>" />
				</td>
			</tr>

			<tr>
				<th scope="row"><label class="civicrm_directory_settings_label" for="civicrm_directory_google_maps_height"><?php esc_html_e( 'Default Google Maps height in pixels', 'civicrm-directory' ); ?></label></th>
				<td>
					<input type="text" id="civicrm_directory_google_maps_height" name="civicrm_directory_google_maps_height" value="<?php echo esc_attr( $google_maps_height ); ?>" />
					<p class="description"><?php esc_html_e( 'Individual Directories can override this setting.', 'civicrm-directory' ); ?></p>
				</td>
			</tr>

		</table>

		<hr>

		<p class="submit">
			<input class="button-primary" type="submit" id="civicrm_directory_settings_submit" name="civicrm_directory_settings_submit" value="<?php esc_attr_e( 'Save Changes', 'civicrm-directory' ); ?>" />
		</p>

	</form>

</div><!-- /.wrap -->
