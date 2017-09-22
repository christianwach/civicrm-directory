<!-- assets/templates/theme/civicrm-directory/directory-contact.php -->
<div class="civicrm-directory directory-contact">

	<p><?php _e( 'This is a contact.', 'civicrm-directory' ); ?></p>

	<?php civicrm_directory_map(); ?>

	<section class="contact-details">

		<?php if ( $this->contact !== false ) : ?>
			<pre><?php print_r( $this->contact ); ?></pre>
		<?php endif; ?>

	</section><!-- /.listing -->

</div><!-- /.civicrm-directory -->



