<?php
/**
 * Directory Browse Template.
 *
 * Handles Directory Browse markup.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?><!-- assets/templates/theme/civicrm-directory/directory-browse.php -->
<section class="browse">

	<h3><?php esc_html_e( 'Browse by first letter', 'civicrm-directory' ); ?></h3>

	<p><?php echo $first_letters; ?></p>

</section><!-- /.browse -->
