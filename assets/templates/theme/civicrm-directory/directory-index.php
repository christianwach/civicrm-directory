<?php
/**
 * Directory Index Template.
 *
 * Handles Directory Index markup.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?><!-- assets/templates/theme/civicrm-directory/directory-index.php -->
<div class="civicrm-directory directory-index">

	<?php civicrm_directory_browser(); ?>

	<?php civicrm_directory_search(); ?>

	<?php civicrm_directory_map(); ?>

	<?php civicrm_directory_listing(); ?>

</div><!-- /.civicrm-directory -->
