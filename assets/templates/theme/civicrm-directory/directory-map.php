<?php
/**
 * Directory Map Template.
 *
 * Handles Directory Map markup.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?><!-- assets/templates/theme/civicrm-directory/directory-map.php -->
<section class="map">

	<h3><?php esc_html_e( 'Map', 'civicrm-directory' ); ?></h3>

	<div id="map-canvas" style="width: 100%; height: <?php echo $height; ?>px;"></div>

</section><!-- /.map -->
