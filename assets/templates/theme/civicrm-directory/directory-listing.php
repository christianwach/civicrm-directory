<?php
/**
 * Directory Listing Template.
 *
 * Handles Directory Listing markup.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?><!-- assets/templates/theme/civicrm-directory/directory-listing.php -->
<section class="listing">

	<?php if ( ! empty( $feedback ) ) : ?>
		<h3><?php echo $feedback; ?></h3>
	<?php endif; ?>

	<?php if ( ! empty( $listing ) ) : ?>
		<?php echo $listing; ?>
	<?php endif; ?>

</section><!-- /.listing -->
