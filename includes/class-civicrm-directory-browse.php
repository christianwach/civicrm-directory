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
	 */
	public function insert_markup() {

		// construct markup
		$markup = '
			<section class="browse">
				<h4>' . __( 'Browse by first letter', 'civicrm-directory' ) . '</h4>
				' . $this->get_chars() . '
			</section>
		';

		// print to screen
		echo $markup;

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
			$href = $url . '/?filter=name&name_id=' . $char;

			// maybe set additional class
			$class = trim( $_GET['name_id'] ) == $char ? ' current' : '';

			// construct anchor and add to letters
			$letters[] = '<a href="' . esc_url( $href ) . '" class="name-link' . $class . '">' . $char . '</a>';

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



