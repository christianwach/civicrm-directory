<?php
/**
 * Directory Details Template.
 *
 * Handles Directory Details markup.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?><!-- assets/templates/theme/civicrm-directory/directory-details.php -->
<div class="directory-details">

	<!-- Contact Core Fields -->
	<?php if ( isset( $contact['core'] ) ) : ?>
		<h3><?php esc_html_e( 'Core', 'civicrm-directory' ); ?></h3>
		<ul>
		<?php foreach ( $contact['core'] as $field ) : ?>
			<li><?php echo $field['label']; ?>: <?php echo $field['value']; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<!-- Contact Custom Fields -->
	<?php if ( isset( $contact['custom'] ) ) : ?>
		<h3><?php esc_html_e( 'Custom', 'civicrm-directory' ); ?></h3>
		<ul>
		<?php foreach ( $contact['custom'] as $field ) : ?>
			<li><?php echo $field['label']; ?>: <?php echo $field['value']; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<!-- Contact Emails -->
	<?php if ( isset( $contact['email'] ) ) : ?>
		<h3><?php esc_html_e( 'Email', 'civicrm-directory' ); ?></h3>
		<ul>
		<?php foreach ( $contact['email'] as $field ) : ?>
			<li><?php echo $field['label']; ?>: <?php echo $field['value']; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<!-- Contact Websites -->
	<?php if ( isset( $contact['website'] ) ) : ?>
		<h3><?php esc_html_e( 'Website', 'civicrm-directory' ); ?></h3>
		<ul>
		<?php foreach ( $contact['website'] as $field ) : ?>
			<li><?php echo $field['label']; ?>: <a href="<?php echo $field['value']; ?>"><?php echo $field['value']; ?></a></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<!-- Contact Phones -->
	<?php if ( isset( $contact['phone'] ) ) : ?>
		<h3><?php esc_html_e( 'Phone', 'civicrm-directory' ); ?></h3>
		<ul>
		<?php foreach ( $contact['phone'] as $field ) : ?>
			<li><?php echo $field['label']; ?> <?php echo $field['value']; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<!-- Contact Addresses -->
	<?php if ( isset( $contact['address'] ) ) : ?>
		<h3><?php esc_html_e( 'Address', 'civicrm-directory' ); ?></h3>
		<ul>
		<?php foreach ( $contact['address'] as $location ) : ?>
			<li>
				<h4><?php echo $location['label']; ?></h4>
				<ul>
				<?php foreach ( $location['address'] as $address ) : ?>
					<li><?php echo $address['label']; ?>: <?php echo $address['value']; ?></li>
				<?php endforeach; ?>
				</ul>
			</li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

</div><!-- /.directory-details -->
