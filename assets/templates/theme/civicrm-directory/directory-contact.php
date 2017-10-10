<!-- assets/templates/theme/civicrm-directory/directory-contact.php -->
<div class="civicrm-directory directory-contact">

	<p class="civicrm-directory-nav"><a href="<?php civicrm_directory_url(); ?>">&larr; <?php echo sprintf( __( 'Back to %s', 'civicrm-directory' ), civicrm_directory_title_get() ); ?></a></p>

	<?php civicrm_directory_map(); ?>

	<section class="contact-details">

		<?php civicrm_directory_contact_details(); ?>

	</section><!-- /.listing -->

</div><!-- /.civicrm-directory -->



