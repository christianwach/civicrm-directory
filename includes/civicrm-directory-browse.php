<?php

/**
 * CiviCRM Directory Browse Class.
 *
 * A class that encapsulates Directory browsing functionality.
 *
 * @since 0.1
 */
class CiviCRM_Directory_Browse {

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
		add_action( 'wp_ajax_civicrm_directory_first_letter', array( $this, 'get_data' ) );
		add_action( 'wp_ajax_nopriv_civicrm_directory_first_letter', array( $this, 'get_data' ) );

	}



	/**
	 * Insert the browse markup.
	 *
	 * @since 0.1
	 *
	 * @param array $data The configuration data.
	 */
	public function insert_markup( $data = array() ) {

		// get chars
		$first_letters = $this->get_chars();

		// get template
		$template = $this->plugin->template->find_file( 'civicrm-directory/directory-browse.php' );

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
			'civicrm-directory-browse-js',
			CIVICRM_DIRECTORY_URL . 'assets/js/civicrm-directory-browse.js',
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
			'civicrm-directory-browse-js',
			'CiviCRM_Directory_Browse_Settings',
			$vars
		);

	}



	/**
	 * Get the markup for browsing by first letter.
	 *
	 * @since 0.1
	 *
	 * @return str $filter The markup for the first letter filter.
	 */
	public function get_chars() {

		// init letters array
		$letters = array();

		// set base url
		$url = get_permalink( get_the_ID() );

		// construct array of chars
		$chars = array_merge( range( 'A', 'Z' ), range( '0', '9' ) );

		// construct each link
		foreach( $chars AS $char ) {

			// set href
			$href = $url . '/?browse=letter&name_id=' . $char;

			// maybe set additional class
			$class = '';
			if ( isset( $_GET['name_id'] ) ) {
				if ( trim( $_GET['name_id'] ) == $char ) $class = ' current';
			}

			// construct anchor and add to letters
			$letters[] = '<a href="' . esc_url( $href ) . '" class="first-letter-link' . $class . '">' . $char . '</a>';

		}

			// set href
			$href = $url . '/?browse=letter&name_id=ALL';

		// add anchor for all letters
		$letters[] = '<a href="' . esc_url( $href ) . '" class="first-letter-link">' . __( 'ALL', 'civicrm-directory' ) . '</a>';

		// construct character filter
		$filter = implode( ' ', $letters );

		// --<
		return $filter;

	}



	/**
	 * Get the CiviCRM data for the first letter.
	 *
	 * @since 0.1.1
	 */
	public function get_data() {

		// get letter
		$letter = isset( $_POST['first_letter'] ) ? trim( $_POST['first_letter'] ) : '';

		// sanitise if this is this not the 'ALL' filter
		if ( $letter !== 'ALL' ) {
			$letter = substr( $letter, 0, 1 );
		}

		// init data
		$data = array(
			'letter' => $letter,
		);

		// get post ID
		$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : '';

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
		if ( ! empty( $group_id ) AND $letter !== 'ALL' ) {

			/*
			 * These queries will be chosen optionally based on the contact types
			 * that have been enabled for this Directory.
			 *
			 * Including them all for now...
			 */

			// get contacts in this group filtered by first letter
			$contacts = $plugin->admin->contacts_get_for_group(
				$group_id,
				'first_letter',
				'last_name',
				$letter
			);

			// get households in this group filtered by first letter
			$households = $plugin->admin->contacts_get_for_group(
				$group_id,
				'first_letter',
				'household_name',
				$letter
			);

			// get organisations in this group filtered by first letter
			$organisations = $plugin->admin->contacts_get_for_group(
				$group_id,
				'first_letter',
				'organization_name',
				$letter
			);

			// combine the results
			$results = array_merge( $contacts, $households, $organisations );

		} else {

			// get all contacts in this group
			$results = $plugin->admin->contacts_get_for_group( $group_id, 'all', '', '' );

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
				'list_item' => $this->get_item_markup( $contact ),
			);

		}

		// add to data array
		$data['locations'] = $locations;

		// init markup
		$markup = '<h3>' . __( 'Results' ) . '</h3>';

		// no need for feedback with ALL
		if ( $letter !== 'ALL'  ) {

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
					__( 'No results found for %s', 'civicrm-directory' ),
					$letter
				);

				// no results markup
				$markup .= '<p>' . $message . '<p>';

			}

		} else {

			// ALL results markup
			$markup .= '<p>' . __( 'Please choose a filter.', 'civicrm-directory' ) . '<p>';

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
 * Render the browse section for a directory.
 *
 * @since 0.1
 */
function civicrm_directory_browser() {

	// render browse section now
	civicrm_directory()->browse->insert_markup();

}



