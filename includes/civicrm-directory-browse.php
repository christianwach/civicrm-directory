<?php
/**
 * Browse Class.
 *
 * Handles Directory browsing functionality.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Browse Class.
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

		// Store plugin reference.
		$this->plugin = $parent;

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// Add AJAX handlers.
		add_action( 'wp_ajax_civicrm_directory_first_letter', [ $this, 'get_data' ] );
		add_action( 'wp_ajax_nopriv_civicrm_directory_first_letter', [ $this, 'get_data' ] );

		// Look for GET variable.
		add_action( 'init', [ $this, 'browse_query_exists' ] );

	}

	//##########################################################################

	/**
	 * Test for browse query.
	 *
	 * @since 0.1.3
	 */
	public function browse_query_exists() {

		// Bail if not set.
		if ( ! isset( $_GET['civicrm_directory_first_letter'] ) ) {
			return;
		}

		// Override the initial map query.
		add_filter( 'civicrm_directory_map_contacts', [ $this, 'map_query_filter' ] );

		// Give the listings some initial content.
		add_filter( 'civicrm_directory_listing_markup', [ $this, 'listing_markup' ] );

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

		// Sanitize browse query.
		$letter = sanitize_text_field( trim( $_GET['civicrm_directory_first_letter'] ) );

		// Do query if it hasn't already been done.
		if ( ! isset( $this->results ) ) {
			$this->results = $this->get_results( get_the_ID(), $letter );
		}

		// Override if there are some results.
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
	public function listing_markup( $data = [] ) {

		// Sanitize browse query.
		$letter = sanitize_text_field( trim( $_GET['civicrm_directory_first_letter'] ) );

		// Do query if it hasn't already been done.
		if ( ! isset( $this->results ) ) {
			$this->results = $this->get_results( get_the_ID(), $letter );
		}

		// Create markup if there are some results.
		if ( ! empty( $this->results ) ) {

			// Init markup.
			$markup = '';

			// If not 'ALL' then show a heading.
			if ( $letter !== 'ALL' ) {
				$markup .= '<h3>' . __( 'Results', 'civicrm-directory' ) . '</h3>';
			}

			// Add listing.
			$markup .= $this->get_listing_markup( $this->results, $letter, $post_id );

			// Add to array.
			$data['listing'] = $markup;

		}

		// --<
		return $data;

	}

	//##########################################################################

	/**
	 * Insert the browse markup.
	 *
	 * @since 0.1
	 *
	 * @param array $data The configuration data.
	 */
	public function insert_markup( $data = [] ) {

		// Get chars.
		$first_letters = $this->get_chars();

		// Get template.
		$template = $this->plugin->template->find_file( 'civicrm-directory/directory-browse.php' );

		// Include the template part.
		include $template;

		// Enqueue Javascript.
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

		// Enqueue custom javascript.
		wp_enqueue_script(
			'civicrm-directory-browse-js',
			CIVICRM_DIRECTORY_URL . 'assets/js/civicrm-directory-browse.js',
			[ 'jquery' ],
			CIVICRM_DIRECTORY_VERSION,
			true // In footer.
		);

		// Init localisation.
		$localisation = [];

		/// Init settings.
		$settings = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'post_id' => get_the_ID(),
			'ajax_loader' => CIVICRM_DIRECTORY_URL . 'assets/images/ajax-loader.gif',
		];

		// Localisation array.
		$vars = [
			'localisation' => $localisation,
			'settings' => $settings,
		];

		// Localise the WordPress way.
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

		// Init letters array.
		$letters = [];

		// Set base url.
		$url = esc_url( get_permalink( get_the_ID() ) );

		// Construct array of chars.
		$chars = array_merge( range( 'A', 'Z' ), range( '0', '9' ) );

		// Construct each link.
		foreach ( $chars as $char ) {

			// Set href.
			$href = add_query_arg( 'civicrm_directory_first_letter', $char, $url );

			// Maybe set additional class.
			$class = '';
			if ( isset( $_GET['letter'] ) ) {
				if ( trim( $_GET['letter'] ) == $char ) {
$class = ' current';
				}
			}

			// Construct anchor and add to letters.
			$letters[] = '<a href="' . esc_url( $href ) . '" class="first-letter-link' . $class . '">' . $char . '</a>';

		}

		// Set href for 'ALL'.
		$href = $url;

		// Add anchor for all letters.
		$letters[] = '<a href="' . esc_url( $href ) . '" class="first-letter-link">' . __( 'ALL', 'civicrm-directory' ) . '</a>';

		// Construct character filter.
		$filter = implode( ' ', $letters );

		// Wrap in identifier.
		$filter = '<span class="civicrm_directory_browse">' . $filter . '</span>';

		// --<
		return $filter;

	}

	/**
	 * Get the CiviCRM data for the first letter.
	 *
	 * @since 0.1.1
	 */
	public function get_data() {

		// Get letter.
		$letter = isset( $_POST['first_letter'] ) ? trim( $_POST['first_letter'] ) : '';

		// Sanitise if this is this not the 'ALL' filter.
		if ( $letter !== 'ALL' ) {
			$letter = substr( $letter, 0, 1 );
		}

		// Init data.
		$data = [
			'letter' => $letter,
		];

		// Get sanitised post ID.
		$post_id = isset( $_POST['post_id'] ) ? absint( trim( $_POST['post_id'] ) ) : '';

		// Do query.
		$results = $this->get_results( $post_id, $letter );

		// Build locations array.
		$locations = [];
		foreach ( $results as $contact ) {

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
				'permalink' => esc_url( trailingslashit( get_permalink( $post_id ) ) . 'entry/' . $contact['id'] ),
				'list_item' => $this->get_item_markup( $contact, $post_id ),
			];

		}

		// Add to data array.
		$data['locations'] = $locations;

		// Init markup.
		$markup = '';

		// If not 'ALL' then show a heading.
		if ( $letter !== 'ALL' ) {
			$markup .= '<h3>' . __( 'Results', 'civicrm-directory' ) . '</h3>';
		}

		// Get listing markup.
		$markup .= $this->get_listing_markup( $results, $letter, $post_id );

		// Add to data array.
		$data['listing'] = $markup;

		// Send data to browser.
		$this->send_data( $data );

	}

	/**
	 * Get the CiviCRM data for the browse string.
	 *
	 * @since 0.1.3
	 *
	 * @param int $post_id The numeric ID of the current post/page.
	 * @param str $letter The letter searched for.
	 * @return array $results The array of contact data.
	 */
	public function get_results( $post_id, $letter ) {

		// Init return.
		$results = [];

		// Get plugin reference.
		$plugin = civicrm_directory();

		// Get group ID from post meta.
		$group_id = $plugin->cpt_meta->group_id_get( $post_id );

		// Sanity check.
		if ( empty( $group_id ) ) {
			return $results;
		}

		// Get contact types from post meta.
		$contact_types = $plugin->cpt_meta->contact_types_get( $post_id );

		// Sanity check.
		if ( empty( $contact_types ) ) {
			return $results;
		}

		// Sanity check.
		if ( $letter !== 'ALL' ) {

			// Get individuals in this group filtered by first letter.
			$individuals = [];
			if ( in_array( 'Individual', $contact_types ) ) {
				$individuals = $plugin->civi->contacts_get_for_group(
					$group_id,
					'Individual',
					'first_letter',
					'last_name',
					$letter
				);
			}

			// Get households in this group filtered by first letter.
			$households = [];
			if ( in_array( 'Household', $contact_types ) ) {
				$households = $plugin->civi->contacts_get_for_group(
					$group_id,
					'Household',
					'first_letter',
					'household_name',
					$letter
				);
			}

			// Get organisations in this group filtered by first letter.
			$organisations = [];
			if ( in_array( 'Organization', $contact_types ) ) {
				$organisations = $plugin->civi->contacts_get_for_group(
					$group_id,
					'Organization',
					'first_letter',
					'organization_name',
					$letter
				);
			}

			// Combine the results.
			$results = array_merge( $individuals, $households, $organisations );

		} else {

			// Get all contacts in this group.
			$results = $plugin->civi->contacts_get_for_group( $group_id, $contact_types, 'all', '', '' );

		}

		// --<
		return $results;

	}

	/**
	 * Get the listing markup for the given results.
	 *
	 * @since 0.1.3
	 *
	 * @param array $results The array of contact data.
	 * @param str $letter The letter that was filtered by.
	 * @param int $post_id The numeric ID of the current post/page.
	 * @return str $markup The listing markup.
	 */
	public function get_listing_markup( $results, $letter = 'ALL', $post_id ) {

		// Init return.
		$markup = '';

		// No need for feedback with ALL.
		if ( $letter !== 'ALL' ) {

			// If we have results.
			if ( count( $results ) > 0 ) {

				// Build listings array.
				$listings = [];
				foreach ( $results as $contact ) {
					$listings[] = $this->get_item_markup( $contact, $post_id );
				}

				// Build markup.
				$markup .= '<ul><li>';
				$markup .= implode( '</li><li>', $listings );
				$markup .= '</li></ul>';

			} else {

				// Contstruct message.
				$message = sprintf(
					/* translators: %s: the letter */
					__( 'No results found for %s', 'civicrm-directory' ),
					$letter
				);

				// No results markup.
				$markup .= '<p>' . $message . '<p>';

			}

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
	 * @param int $post_id The numeric ID of the current post/page.
	 * @return str $markup The contact markup.
	 */
	public function get_item_markup( $contact, $post_id ) {

		// Construct permalink.
		$permalink = esc_url( trailingslashit( get_permalink( $post_id ) ) . 'entry/' . $contact['id'] );

		// Build markup.
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

		// Is this an AJAX request?
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			// Set reasonable headers.
			header( 'Content-type: text/plain' );
			header( 'Cache-Control: no-cache' );
			header( 'Expires: -1' );

			// Echo.
			echo json_encode( $data );

			// Die.
			exit();

		}

	}

}

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

