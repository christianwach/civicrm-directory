<?php

/**
 * CiviCRM Directory Search Class.
 *
 * A class that encapsulates membership levels functionality.
 *
 * @since 0.1
 */
class CiviCRM_Directory_Search {

	/**
	 * Plugin (calling) object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $plugin The plugin object
	 */
	public $plugin;



	/**
	 * Constructor.
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
	 * Register WordPress hooks.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// add AJAX handlers
		add_action( 'wp_ajax_civicrm_directory_search', array( $this, 'get_data' ) );
		add_action( 'wp_ajax_nopriv_civicrm_directory_search', array( $this, 'get_data' ) );

	}



	/**
	 * Insert the markup.
	 *
	 * @since 0.1
	 *
	 * @param array $data The configuration data.
	 */
	public function insert_markup( $data = array() ) {

		// get URL to submit to
		$url = get_permalink( get_the_ID() );

		// get template
		$template = $this->plugin->template->find_file( 'civicrm-directory/directory-search.php' );

		// include the template part
		include( $template );

		// enqueue Javascript
		$this->enqueue_script( $data );

	}



	/**
	 * Enqueue and configure Javascript.
	 *
	 * @since 0.1
	 *
	 * @param array $data The configuration data.
	 */
	public function enqueue_script( $data ) {

		// enqueue custom javascript
		wp_enqueue_script(
			'civicrm-directory-search-js',
			CIVICRM_DIRECTORY_URL . 'assets/js/civicrm-directory-search.js',
			array( 'jquery' ),
			CIVICRM_DIRECTORY_VERSION,
			true // in footer
		);

		// init localisation
		$localisation = array();

		/// init settings
		$settings = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'post_id' => get_the_ID(),
		);

		// localisation array
		$vars = array(
			'localisation' => $localisation,
			'settings' => $settings,
		);

		// localise the WordPress way
		wp_localize_script(
			'civicrm-directory-search-js',
			'CiviCRM_Directory_Search_Settings',
			$vars
		);

	}



	/**
	 * Get the CiviCRM data for the search string.
	 *
	 * @since 0.1.1
	 */
	public function get_data() {

		/*
		error_log( print_r( array(
			'method' => __METHOD__,
			'POST' => $_POST,
		), true ) );
		*/

		// get search
		$search = isset( $_POST['search'] ) ? trim( $_POST['search'] ) : '';

		// init data
		$data = array(
			'search' => $search,
		);

		// get post ID
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : '';

		// sanitise
		$post_id = absint( trim( $post_id ) );

		$plugin = civicrm_directory();

		// set key
		$db_key = '_' . $plugin->metaboxes->group_id_meta_key;

		// default to empty
		$group_id = '';

		// get value if the custom field already has one
		$existing = get_post_meta( $post_id, $db_key, true );
		if ( false !== $existing ) {
			$group_id = get_post_meta( $post_id, $db_key, true );
		}

		// sanity check
		if ( ! empty( $group_id ) ) {

			// set key
			$db_key = '_' . $plugin->metaboxes->contact_types_meta_key;

			// default to empty
			$contact_types = array();

			// get value if the custom field already has one
			$existing = get_post_meta( $post_id, $db_key, true );
			if ( false !== $existing ) {
				$contact_types = get_post_meta( $post_id, $db_key, true );
			}

			// get individuals in this group filtered by first letter
			$individuals = array();
			if ( in_array( 'Individual', $contact_types ) ) {
				$individuals = $plugin->civi->contacts_get_for_group(
					$group_id,
					'Individual',
					'name',
					'last_name',
					$search
				);
			}

			// get households in this group filtered by first letter
			$households = array();
			if ( in_array( 'Household', $contact_types ) ) {
				$households = $plugin->civi->contacts_get_for_group(
					$group_id,
					'Household',
					'name',
					'household_name',
					$search
				);
			}

			// get organisations in this group filtered by first letter
			$organisations = array();
			if ( in_array( 'Organization', $contact_types ) ) {
				$organisations = $plugin->civi->contacts_get_for_group(
					$group_id,
					'Organization',
					'name',
					'organization_name',
					$search
				);
			}

			// combine the results
			$results = array_merge( $individuals, $households, $organisations );

			/*
			error_log( print_r( array(
				'method' => __METHOD__,
				'contact_types' => $contact_types,
				'results' => $results,
			), true ) );
			*/

		}

		// build locations array
		$locations = array();
		foreach( $results AS $contact ) {

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
				'permalink' => get_permalink( $post_id ),
			);

		}

		// add to data array
		$data['locations'] = $locations;

		// init markup
		$markup = '<h3>' . __( 'Results' ) . '</h3>';

		// if we have results
		if ( count( $results ) > 0 ) {

			// build listings array
			$listings = array();
			foreach( $results AS $contact ) {
				$listings[] = $this->get_item_markup( $contact );
			}

			// build markup
			$markup .= '<ul><li>';
			$markup .= implode( '</li><li>', $listings );
			$markup .= '</li></ul>';

		} else {

			// contstruct message
			$message = sprintf(
				__( 'No results found for "%s"', 'civicrm-directory' ),
				$search
			);

			// no results markup
			$markup .= '<p>' . $message . '</p>';

		}

		// add to data array
		$data['listing'] = $markup;

		/*
		error_log( print_r( array(
			'method' => __METHOD__,
			'POST' => $_POST,
			'group_id' => $group_id,
			'data' => $data,
		), true ) );
		*/

		// send data to browser
		$this->send_data( $data );

	}



	/**
	 * Create markup for a listing item.
	 *
	 * @since 0.1.1
	 *
	 * @param array $contact The contact to create markup for.
	 * @return str $markup The contact markup.
	 */
	public function get_item_markup( $contact ) {

		// build markup
		$markup = '<a href="#">' . esc_html( $contact['display_name'] ) . '</a>';

		// --<
		return $markup;

	}



	/**
	 * Send JSON data to the browser.
	 *
	 * @since 0.2.1
	 *
	 * @param array $data The data to send.
	 */
	private function send_data( $data ) {

		// is this an AJAX request?
		if ( defined( 'DOING_AJAX' ) AND DOING_AJAX ) {

			// set reasonable headers
			header('Content-type: text/plain');
			header("Cache-Control: no-cache");
			header("Expires: -1");

			// echo
			echo json_encode( $data );

			// die
			exit();

		}

	}



} // class ends



/**
 * Render the search section for a directory.
 *
 * @since 0.1
 */
function civicrm_directory_search() {

	// render search section now
	civicrm_directory()->search->insert_markup();

}




