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

		// print markup
		echo '
			<section class="browse">
				<h3>' . __( 'Browse by first letter', 'civicrm-directory' ) . '</h3>
				<p>' . $this->get_chars() . '</p>
			</section>
		';

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
			$class = trim( $_GET['name_id'] ) == $char ? ' current' : '';

			// construct anchor and add to letters
			$letters[] = '<a href="' . esc_url( $href ) . '" class="first-letter-link' . $class . '">' . $char . '</a>';

		}

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
		$letter = isset( $_POST['first_letter'] ) ? $_POST['first_letter'] : '';

		// sanitise
		$letter = substr( trim( $letter ), 0, 1 );

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

		/*
		error_log( print_r( array(
			'method' => __METHOD__,
			'POST' => $_POST,
			'group_id' => $group_id,
		), true ) );
		*/

		// sanity check
		if ( ! empty( $group_id ) ) {

			// get contacts in this group filtered by first letter
			$data['contacts'] = $plugin->admin->contacts_get_for_group( $group_id, 'first_letter', $letter );

		}

		// send data to browser
		$this->send_data( $data );

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



