<!-- assets/templates/theme/civicrm-directory/directory-details.php -->
<div class="directory-details">

	<?php if( isset( $contact['core'] ) ) : ?>
		<h3><?php _e( 'Core', 'civicrm-directory' ); ?></h3>
		<ul>
		<?php foreach( $contact['core'] AS $field ) : ?>
			<li><?php echo $field['label']; ?>: <?php echo $field['value']; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if( isset( $contact['custom'] ) ) : ?>
		<h3><?php _e( 'Custom', 'civicrm-directory' ); ?></h3>
		<ul>
		<?php foreach( $contact['custom'] AS $field ) : ?>
			<li><?php echo $field['label']; ?>: <?php echo $field['value']; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if( isset( $contact['email'] ) ) : ?>
		<h3><?php _e( 'Email', 'civicrm-directory' ); ?></h3>
		<ul>
		<?php foreach( $contact['email'] AS $field ) : ?>
			<li><?php echo $field['label']; ?>: <?php echo $field['value']; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if( isset( $contact['website'] ) ) : ?>
		<h3><?php _e( 'Website', 'civicrm-directory' ); ?></h3>
		<ul>
		<?php foreach( $contact['website'] AS $field ) : ?>
			<li><?php echo $field['label']; ?>: <?php echo $field['value']; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if( isset( $contact['phone'] ) ) : ?>
		<h3><?php _e( 'Phone', 'civicrm-directory' ); ?></h3>
		<ul>
		<?php foreach( $contact['phone'] AS $field ) : ?>
			<li><?php echo $field['label']; ?>: <?php echo $field['value']; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if( isset( $contact['address'] ) ) : ?>
		<h3><?php _e( 'Address', 'civicrm-directory' ); ?></h3>
		<ul>
		<?php foreach( $contact['address'] AS $location ) : ?>
			<li>
				<h4><?php echo $location['label']; ?></h4>
				<ul>
				<?php foreach( $location['address'] AS $address ) : ?>
					<li><?php echo $address['label']; ?>: <?php echo $address['value']; ?></li>
				<?php endforeach; ?>
				</ul>
			</li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

</div><!-- /.directory-details -->



