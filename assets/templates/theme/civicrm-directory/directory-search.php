<!-- assets/templates/theme/civicrm-directory/directory-search.php -->
<section class="search">

	<h3><?php _e( 'Search', 'civicrm-directory' ); ?></h3>

	<p><?php _e( 'You can search by name or keyword.', 'civicrm-directory' ); ?></p>

	<form action="<?php echo $url; ?>" method="post" id="civicrm_directory_search">
		<label for="civicrm_directory_search_name"><?php esc_html_e( 'Search by Name', 'civicrm-directory' ); ?></label>
		<input type="text" id="civicrm_directory_search_name" name="civicrm_directory_search_name" value="">
		<input type="submit" id="civicrm_directory_search_submit" value="<?php esc_attr_e( 'Search', 'civicrm-directory' ); ?>">
	</form>

</section><!-- /.search -->



