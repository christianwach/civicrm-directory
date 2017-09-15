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



