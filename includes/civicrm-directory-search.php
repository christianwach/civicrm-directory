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

		// look for GET variable
		add_action( 'init', array( $this, 'search_query_exists' ) );

	}



	/**
	 * Test for search query.
	 *
	 * @since 0.1.3
	 */
	public function search_query_exists() {

		// bail if not set
		if ( ! isset( $_GET['civicrm_directory_search_string'] ) ) return;

		// sanitize search query
		$search = sanitize_text_field( trim( $_GET['civicrm_directory_search_string'] ) );

		// bail if empty
		if ( empty( $search ) ) return;

		// override the initial map query
		add_filter( 'civicrm_directory_map_contacts', array( $this, 'map_query_filter' ) );

		// give the listings some initial content
		add_filter( 'civicrm_directory_listing_markup', array( $this, 'listing_markup' ) );

	}



	/**
	 * Override the initial map query.
	 *
	 * @since 0.1.3
	 *
	 * @param array $contacts The contacts retrieved from CiviCRM.
	 * @return array $contacts The modified contacts retrieved from CiviCRM.
	 */
	public function map_query_filter( $contacts ) {

		// sanitize search query
		$search = sanitize_text_field( trim( $_GET['civicrm_directory_search_string'] ) );

		// do query if it hasn't already been done
		if ( ! isset( $this->results ) ) {
			$this->results = $this->get_results( get_the_ID(), $search );
		}

		// override if there are some results
		if ( ! empty( $this->results ) ) {
			$contacts = $this->results;
		}

		// --<
		return $contacts;

	}



	/**
	 * Create the listing markup.
	 *
	 * @since 0.1.3
	 *
	 * @param array $data The configuration data.
	 * @return array $data The configuration data.
	 */
	public function listing_markup( $data = array() ) {

		// sanitize search query
		$search = sanitize_text_field( trim( $_GET['civicrm_directory_search_string'] ) );

		// do query if it hasn't already been done
		if ( ! isset( $this->results ) ) {
			$this->results = $this->get_results( get_the_ID(), $search );
		}

		// create markup if there are some results
		if ( ! empty( $this->results ) ) {

			// init markup
			$markup = '<h3>' . __( 'Results', 'civicrm-directory' ) . '</h3>';

			// add listing
			$markup .= $this->get_listing_markup( $this->results, $post_id );

			// add to array
			$data['listing'] = $markup;

		}

		// --<
		return $data;

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
		$url = esc_url( get_permalink( get_the_ID() ) );

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
			'ajax_loader' => CIVICRM_DIRECTORY_URL . 'assets/images/ajax-loader.gif',
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

		// get search
		$search = isset( $_POST['search'] ) ? trim( $_POST['search'] ) : '';

		// init data
		$data = array(
			'search' => $search,
		);

		// get sanitised post ID
		$post_id = isset( $_POST['post_id'] ) ? absint( trim( $_POST['post_id'] ) ) : '';

		// do query
		$results = $this->get_results( $post_id, $search );

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
				'permalink' => esc_url( trailingslashit( get_permalink( $post_id ) ) . 'entry/' . $contact['id'] ),
			);

		}

		// add to data array
		$data['locations'] = $locations;

		// init markup
		$markup = '';

		// init markup
		if ( ! empty( $search ) ) {

			// add heading
			$markup = '<h3>' . __( 'Results', 'civicrm-directory' ) . '</h3>';

			// add listing
			$markup .= $this->get_listing_markup( $results, $post_id );

		}

		// add to data array
		$data['listing'] = $markup;

		// send data to browser
		$this->send_data( $data );

	}



	/**
	 * Get the CiviCRM data for the search string.
	 *
	 * @since 0.1.3
	 *
	 * @param int $post_id The ID of the current post/page.
	 * @param str $search The search string.
	 * @return array $results The array of contact data.
	 */
	public function get_results( $post_id, $search ) {

		// init return
		$results = array();

		// get plugin reference
		$plugin = civicrm_directory();

		// get group ID from post meta
		$group_id = $plugin->cpt_meta->group_id_get( $post_id );

		// sanity check
		if ( empty( $group_id ) ) return $results;

		// get contact types from post meta
		$contact_types = $plugin->cpt_meta->contact_types_get( $post_id );

		// sanity check
		if ( empty( $contact_types ) ) return $results;

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

		// --<
		return $results;

	}



	/**
	 * Get the listing markup for the given results.
	 *
	 * @since 0.1.3
	 *
	 * @param array $results The array of contact data.
	 * @param WP_Post $post The object for the current post/page.
	 * @return str $markup The listing markup.
	 */
	public function get_listing_markup( $results, $post_id ) {

		// init return
		$markup = '';

		// if we have results
		if ( count( $results ) > 0 ) {

			// build listings array
			$listings = array();
			foreach( $results AS $contact ) {
				$listings[] = $this->get_item_markup( $contact, $post_id );
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

		// --<
		return $markup;

	}



	/**
	 * Create markup for a listing item.
	 *
	 * @since 0.1.1
	 *
	 * @param array $contact The contact to create markup for.
	 * @param WP_Post $post The object for the current post/page.
	 * @return str $markup The contact markup.
	 */
	public function get_item_markup( $contact, $post_id ) {

		// construct permalink
		$permalink = esc_url( trailingslashit( get_permalink( $post_id ) ) . 'entry/' . $contact['id'] );

		// build markup
		$markup = '<a href="' . $permalink . '">' . esc_html( $contact['display_name'] ) . '</a>';

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

	$plugin = civicrm_directory();

	// get search-enabled from post meta
	$search = $plugin->cpt_meta->search_get();

	// sanity check
	if ( ! $search ) return;

	// render search section now
	civicrm_directory()->search->insert_markup();

}




