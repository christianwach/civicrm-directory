<?php

/**
 * CiviCRM Directory Map Class.
 *
 * A class that encapsulates mapping functionality.
 *
 * @since 0.1
 */
class CiviCRM_Directory_Map {

	/**
	 * Plugin (calling) object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $plugin The plugin object.
	 */
	public $plugin;



	/**
	 * Initialises this object.
	 *
	 * @since 0.1
	 *
	 * @param object $parent The parent object.
	 */
	public function __construct( $parent ) {

		// store
		$this->plugin = $parent;

	}



	/**
	 * Insert the map markup.
	 *
	 * @since 0.1
	 *
	 * @param array $data The configuration data for the map.
	 */
	public function insert_map( $data = array() ) {

		// get template
		$template = $this->plugin->template->find_file( 'civicrm-directory/directory-map.php' );

		// include the template part
		include( $template );

		// enqueue Javascript
		$this->enqueue_script( $data );

	}



	/**
	 * Enqueue and configure Javascript needed for mapping functionality.
	 *
	 * @since 0.1
	 *
	 * @param array $data The configuration data for the map.
	 */
	public function enqueue_script( $data ) {

		// set key if not provided
		if ( ! isset( $data['key'] ) ) {
			$data['key'] = $this->plugin->admin->setting_get( 'google_maps_key' );
		}

		// register Google Maps
		wp_register_script(
			'civicrm-directory-googlemap-js',
			set_url_scheme( 'https://maps.googleapis.com/maps/api/js?v=3.exp&key=' . $data['key'] ),
			array(),
			CIVICRM_DIRECTORY_VERSION,
			true // in footer
		);

		// enqueue Google Maps script
		wp_enqueue_script( 'civicrm-directory-googlemap-js' );

		// enqueue custom javascript
		wp_enqueue_script(
			'civicrm-directory-map-js',
			CIVICRM_DIRECTORY_URL . 'assets/js/civicrm-directory-map.js',
			array( 'civicrm-directory-googlemap-js', 'jquery' ),
			CIVICRM_DIRECTORY_VERSION,
			true // in footer
		);

		// init localisation
		$localisation = array(
			'info_window_link_title' => __( 'View Profile', 'civicrm-directory' ),
		);

		// make sure we have latitude and longitude
		if ( ! isset( $data['latitude'] ) ) {
			$data['latitude'] = $this->plugin->admin->setting_get( 'latitude' );
		}
		if ( ! isset( $data['longitude'] ) ) {
			$data['longitude'] = $this->plugin->admin->setting_get( 'longitude' );
		}

		// make sure we have an initial zoom level
		if ( ! isset( $data['zoom'] ) ) {
			$data['zoom'] = $this->plugin->admin->setting_get( 'zoom' );
		}

		// check data for locations
		if ( ! isset( $data['locations'] ) ) {
			$data['locations'] = array();
		}

		/**
		 * Init settings and allow overrides.
		 *
		 * @since 0.1
		 *
		 * @param array The default settings array
		 * @return array The modified settings array
		 */
		$settings = apply_filters( 'civicrm_directory_js_settings', array(
			'zoom' => $data['zoom'],
			'pin_image_url' => CIVICRM_DIRECTORY_URL . 'assets/images/map-pin@2x.png',
			'latitude' => $data['latitude'],
			'longitude' => $data['longitude'],
			'locations' => $data['locations'],
		) );

		// localisation array
		$vars = array(
			'localisation' => $localisation,
			'settings' => $settings,
		);

		// localise the WordPress way
		wp_localize_script(
			'civicrm-directory-map-js',
			'CiviCRM_Directory_Map_Settings',
			$vars
		);

	}

} // class ends



/**
 * Render a map for a directory.
 *
 * @since 0.1
 */
function civicrm_directory_map() {

	$plugin = civicrm_directory();

	// set key
	$db_key = '_' . $plugin->metaboxes->group_id_meta_key;

	// default to empty
	$group_id = '';

	// get value if the custom field already has one
	$existing = get_post_meta( get_the_ID(), $db_key, true );
	if ( false !== $existing ) {
		$group_id = get_post_meta( get_the_ID(), $db_key, true );
	}

	// sanity check
	if ( empty( $group_id ) ) return;

	// set key
	$db_key = '_' . $plugin->metaboxes->contact_types_meta_key;

	// default to empty
	$contact_types = array();

	// get value if the custom field already has one
	$existing = get_post_meta( get_the_ID(), $db_key, true );
	if ( ! empty( $existing ) ) {
		$contact_types = get_post_meta( get_the_ID(), $db_key, true );
	}

	// sanity check
	if ( empty( $contact_types ) ) return;

	// get contacts in this group
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

	// build locations array
	$locations = array();
	foreach( $contacts AS $contact ) {

		// construct address
		$address_raw = array();
		if ( ! empty( $contact['street_address'] ) ) $address_raw[] = $contact['street_address'];
		if ( ! empty( $contact['city'] ) ) $address_raw[] = $contact['city'];
		if ( ! empty( $contact['state_province_name'] ) ) $address_raw[] = $contact['state_province_name'];
		$address = implode( '<br>', $address_raw );

		// add to locations
		$locations[] = array(
			'latitude' => $contact['geo_code_1'],
			'longitude' => $contact['geo_code_2'],
			'name' => $contact['display_name'],
			'address' => $address,
			'permalink' => trailingslashit( get_permalink( get_the_ID() ) ) . 'view/' . $contact['id'],
		);

	}

	// construct data array
	$data = array(
		'locations' => $locations,
	);

	// render map now
	$plugin->map->insert_map( $data );

}



