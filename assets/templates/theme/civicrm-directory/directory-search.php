<?php
/**
 * Directory Search Template.
 *
 * Handles Directory Search markup.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?><!-- assets/templates/theme/civicrm-directory/directory-search.php -->
<section class="search">

	<h3><?php esc_html_e( 'Search', 'civicrm-directory' ); ?></h3>

	<p><?php esc_html_e( 'You can search by name or keyword.', 'civicrm-directory' ); ?></p>

	<form action="<?php echo $url; ?>" method="get" id="civicrm_directory_search">
		<label for="civicrm_directory_search_string"><?php esc_html_e( 'Search by Name', 'civicrm-directory' ); ?></label>
		<input type="text" id="civicrm_directory_search_string" name="civicrm_directory_search_string" value="">
		<input type="submit" id="civicrm_directory_search_submit" value="<?php esc_attr_e( 'Search', 'civicrm-directory' ); ?>">
	</form>

</section><!-- /.search -->



