<?php
/**
 * Template functions.
 *
 * Collects Template functions in one place.
 *
 * @package CiviCRM_Directory
 * @since 0.2.8
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Render the browse section for a directory.
 *
 * @since 0.1
 */
function civicrm_directory_browser() {

	$plugin = civicrm_directory();

	// Get browse-by-letter-enabled from post meta.
	$letter = $plugin->cpt_meta->letter_get();

	// Sanity check.
	if ( ! $letter ) {
		return;
	}

	// Render browse section now.
	civicrm_directory()->browse->insert_markup();

}

/**
 * Render a map for a directory.
 *
 * @since 0.1
 */
function civicrm_directory_map() {

	$plugin = civicrm_directory();

	// Get mapping-enabled from post meta.
	$mapping = $plugin->cpt_meta->mapping_get();

	// Sanity check.
	if ( ! $mapping ) {
		return;
	}

	// Get group ID from post meta.
	$group_id = $plugin->cpt_meta->group_id_get();

	// Sanity check.
	if ( empty( $group_id ) ) {
			return;
	}

	// Get contact types from post meta.
	$contact_types = $plugin->cpt_meta->contact_types_get();

	// Sanity check.
	if ( empty( $contact_types ) ) {
		return;
	}

	// Get contacts in this group.
	$contacts = $plugin->civi->contacts_get_for_group( $group_id, $contact_types, 'all', '', '' );

	/**
	 * Allow contacts to be filtered.
	 *
	 * @since 0.1.3
	 *
	 * @param array $contacts The unfiltered array of contacts.
	 * @return array $contacts The filtered array of contacts.
	 */
	$contacts = apply_filters( 'civicrm_directory_map_contacts', $contacts );

	// Build locations array.
	$locations = [];
	foreach ( $contacts as $contact ) {

		// Construct address.
		$address_raw = [];
		if ( ! empty( $contact['street_address'] ) ) {
			$address_raw[] = $contact['street_address'];
		}
		if ( ! empty( $contact['city'] ) ) {
			$address_raw[] = $contact['city'];
		}
		if ( ! empty( $contact['state_province_name'] ) ) {
			$address_raw[] = $contact['state_province_name'];
		}
		$address = implode( '<br>', $address_raw );

		// Add to locations.
		$locations[] = [
			'latitude' => $contact['geo_code_1'],
			'longitude' => $contact['geo_code_2'],
			'name' => $contact['display_name'],
			'address' => $address,
			'permalink' => esc_url( trailingslashit( get_permalink( get_the_ID() ) ) . 'entry/' . $contact['id'] ),
		];

	}

	// Construct data array.
	$data = [
		'locations' => $locations,
	];

	// Render map now.
	$plugin->map->insert_map( $data );

}

/**
 * Render the search section for a directory.
 *
 * @since 0.1
 */
function civicrm_directory_search() {

	$plugin = civicrm_directory();

	// Get search-enabled from post meta.
	$search = $plugin->cpt_meta->search_get();

	// Sanity check.
	if ( ! $search ) {
		return;
	}

	// Render search section now.
	civicrm_directory()->search->insert_markup();

}

/**
 * Render the listing section for a directory.
 *
 * @since 0.1.1
 */
function civicrm_directory_listing() {

	// Render browse section now.
	civicrm_directory()->template->insert_markup();

}

/**
 * Echoes the permalink for a directory.
 *
 * @since 0.2.2
 *
 * @param int $post_id The numeric ID of the directory.
 */
function civicrm_directory_url( $post_id = null ) {

	// Echo permalink.
	echo civicrm_directory_url_get( $post_id );

}

/**
 * Return the permalink for a directory.
 *
 * @since 0.2.2
 *
 * @param int $post_id The numeric ID of the directory.
 * @return str $url The permalink for the directory.
 */
function civicrm_directory_url_get( $post_id = null ) {

	// Use current post if none passed.
	if ( is_null( $post_id ) ) {
		$post_id = get_the_ID();
	}

	// Get permalink.
	$url = esc_url( get_permalink( $post_id ) );

	// --<
	return $url;

}

/**
 * Echo the title of a directory.
 *
 * @since 0.2.2
 *
 * @param int $post_id The numeric ID of the directory.
 */
function civicrm_directory_title( $post_id = null ) {

	// Echo title.
	echo civicrm_directory_title_get( $post_id );

}

/**
 * Return the title of a directory.
 *
 * @since 0.2.2
 *
 * @param int $post_id The numeric ID of the directory.
 * @return str $url The name of the directory.
 */
function civicrm_directory_title_get( $post_id = null ) {

	// Pass to template object.
	$title = civicrm_directory()->template->get_the_title( $post_id );

	// --<
	return $title;

}

/**
 * Echo the markup for a contact.
 *
 * @since 0.2.2
 */
function civicrm_directory_contact_details() {

	// Echo contact details.
	echo civicrm_directory_contact_details_get();

}

/**
 * Gets the markup for a contact.
 *
 * @since 0.2.2
 *
 * @return $markup The rendered markup for a contact.
 */
function civicrm_directory_contact_details_get() {

	// Render browse section now.
	return civicrm_directory()->template->the_contact();

}
