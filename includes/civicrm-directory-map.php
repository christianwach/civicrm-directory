<?php
/**
 * Map Class.
 *
 * Handles mapping functionality.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Map Class.
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

		// Store plugin reference.
		$this->plugin = $parent;

	}

	/**
	 * Insert the map markup.
	 *
	 * @since 0.1
	 *
	 * @param array $data The configuration data for the map.
	 */
	public function insert_map( $data = [] ) {

		// Get map height from post meta.
		$height = $this->plugin->cpt_meta->mapping_height_get();

		// Get template.
		$template = $this->plugin->template->find_file( 'civicrm-directory/directory-map.php' );

		// Include the template part.
		include $template;

		// Enqueue Javascript.
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

		// Set key if not provided.
		if ( ! isset( $data['key'] ) ) {
			$data['key'] = $this->plugin->admin->setting_get( 'google_maps_key' );
		}

		// Register Google Maps.
		wp_register_script(
			'civicrm-directory-googlemap-js',
			set_url_scheme( 'https://maps.googleapis.com/maps/api/js?v=3.exp&key=' . $data['key'] ),
			[],
			CIVICRM_DIRECTORY_VERSION,
			true // In footer.
		);

		// Enqueue Google Maps script.
		wp_enqueue_script( 'civicrm-directory-googlemap-js' );

		// Enqueue custom javascript.
		wp_enqueue_script(
			'civicrm-directory-map-js',
			CIVICRM_DIRECTORY_URL . 'assets/js/civicrm-directory-map.js',
			[ 'civicrm-directory-googlemap-js', 'jquery' ],
			CIVICRM_DIRECTORY_VERSION,
			true // In footer.
		);

		// Init localisation.
		$localisation = [
			'info_window_link_title' => __( 'View Profile', 'civicrm-directory' ),
		];

		// Make sure we have latitude and longitude.
		if ( ! isset( $data['latitude'] ) ) {
			$data['latitude'] = $this->plugin->admin->setting_get( 'latitude' );
		}
		if ( ! isset( $data['longitude'] ) ) {
			$data['longitude'] = $this->plugin->admin->setting_get( 'longitude' );
		}

		// Make sure we have an initial zoom level.
		if ( ! isset( $data['zoom'] ) ) {
			$data['zoom'] = $this->plugin->admin->setting_get( 'zoom' );
		}

		// Check data for locations.
		if ( ! isset( $data['locations'] ) ) {
			$data['locations'] = [];
		}

		/**
		 * Init settings and allow overrides.
		 *
		 * @since 0.1
		 *
		 * @param array The default settings array.
		 * @return array The modified settings array.
		 */
		$settings = apply_filters( 'civicrm_directory_js_settings', [
			'zoom' => $data['zoom'],
			'pin_image_url' => '',
			'latitude' => $data['latitude'],
			'longitude' => $data['longitude'],
			'locations' => $data['locations'],
		] );

		// Localisation array.
		$vars = [
			'localisation' => $localisation,
			'settings' => $settings,
		];

		// Localise the WordPress way.
		wp_localize_script(
			'civicrm-directory-map-js',
			'CiviCRM_Directory_Map_Settings',
			$vars
		);

	}

}
