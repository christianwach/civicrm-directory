<?php

/**
 * CiviCRM Directory Template Class.
 *
 * A class that encapsulates templating functionality for CiviCRM Directory.
 *
 * @package CiviCRM_Directory
 */
class CiviCRM_Directory_Template {

	/**
	 * Plugin (calling) object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $plugin The plugin object.
	 */
	public $plugin;

	/**
	 * Viewed contact.
	 *
	 * @since 0.2.1
	 * @access public
	 * @var array $contact The requested contact data.
	 */
	public $contact = false;


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

		// override some page elements
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 5 );

		// filter the content
		add_filter( 'the_content', array( $this, 'directory_render' ) );

	}



	/**
	 * Actions to perform on plugin activation.
	 *
	 * @since 0.1
	 */
	public function activate() {

	}



	/**
	 * Actions to perform on plugin deactivation (NOT deletion).
	 *
	 * @since 0.1
	 */
	public function deactivate() {

	}



	// #########################################################################



	/**
	 * Amend query for directory contact view.
	 *
	 * @since 0.2.1
	 */
	public function pre_get_posts( $query ) {

		// are we viewing a contact?
		if ( ! is_admin() AND $query->is_main_query() AND ! empty( $query->get( 'cividir_contact_id' ) ) ) {

			// sanity check
			$contact_id = absint( $query->get( 'cividir_contact_id' ) );

			// get contact
			$this->contact = $this->plugin->civi->contact_get_by_id( $contact_id );

			// filter the title
			add_filter( 'the_title', array( $this, 'the_title' ), 10, 2 );

			// override the initial map query
			add_filter( 'civicrm_directory_map_contacts', array( $this, 'map_query_filter' ) );

		}

	}



	/**
	 * Override title of the directory page when viewing a contact.
	 *
	 * @since 0.2.1
	 *
	 * @param string $title The existing title.
	 * @param int $id The post ID.
	 * @return string $title The modified title.
	 */
	public function the_title( $title, $id ) {

		global $wp_query;

		// are we viewing a contact?
		if (
			isset( $wp_query->query_vars['cividir_contact_id'] ) AND
			is_numeric( $wp_query->query_vars['cividir_contact_id'] ) AND
			is_singular( 'directory' ) AND
			in_the_loop()
		) {

			// override title if we're successful
			if ( $this->contact !== false ) {
				$title = $this->contact['display_name'];
			}

		}

		// --<
		return $title;

	}



	/**
	 * Override the initial map query.
	 *
	 * @since 0.2.1
	 *
	 * @param array $contacts The contacts retrieved from CiviCRM.
	 * @return array $contacts The modified contacts retrieved from CiviCRM.
	 */
	public function map_query_filter( $contacts ) {

		// override if viewing a contact
		if ( $this->contact !== false ) {
			$contacts = array( $this->contact );
		}

		// --<
		return $contacts;

	}



	/**
	 * Callback filter to display a Directory.
	 *
	 * @param str $content The existing content.
	 * @return str $content The modified content.
	 */
	function directory_render( $content ) {

		global $wp_query;

		// only on canonical Directory pages
		if ( ! is_singular( $this->plugin->cpt->post_type_name ) ) {
			return $content;
		}

		// only for our post type
		if ( get_post_type( get_the_ID() ) !== $this->plugin->cpt->post_type_name ) {
			return $content;
		}

		// are we viewing a contact?
		if ( isset( $wp_query->query_vars['cividir_contact_id'] ) ) {
			$file = 'civicrm-directory/directory-contact.php';
		} else {
			$file = 'civicrm-directory/directory-index.php';
		}

		// get template
		$template = $this->find_file( $file );

		// buffer the template part
		ob_start();
		include( $template );
		$content = ob_get_contents();
		ob_end_clean();

		// --<
		return $content;

	}



	/**
	 * Insert the listing markup.
	 *
	 * @since 0.1
	 *
	 * @param array $data The configuration data.
	 */
	public function insert_markup( $data = array() ) {

		/**
		 * Data can be amended (or created) by callbacks for this filter.
		 *
		 * @since 0.1.3
		 *
		 * @param array $data The existing template data.
		 * @return array $data The modified template data.
		 */
		$data = apply_filters( 'civicrm_directory_listing_markup', $data );

		// init template vars
		$listing = isset( $data['listing'] ) ? $data['listing'] : '';
		$feedback = isset( $data['feedback'] ) ? $data['feedback'] : '';

		// get template
		$template = $this->find_file( 'civicrm-directory/directory-listing.php' );

		// include the template part
		include( $template );

	}



	/**
	 * Find a template given a relative path.
	 *
	 * Example: 'civicrm-directory/directory-search.php'
	 *
	 * @since 0.1
	 *
	 * @param str $template_path The relative path to the template.
	 * @return str|bool $full_path The absolute path to the template, or false on failure.
	 */
	function find_file( $template_path ) {

		// get stack
		$stack = $this->template_stack();

		// constuct templates array
		$templates = array();
		foreach( $stack As $location ) {
			$templates[] = trailingslashit( $location ) . $template_path;
		}

		// let's look for it
		$full_path = false;
		foreach ( $templates AS $template ) {
			if ( file_exists( $template ) ) {
				$full_path = $template;
				break;
			}
		}

		// --<
		return $full_path;

	}



	/**
	 * Construct template stack.
	 *
	 * @since 0.1
	 *
	 * @return array $stack The stack of locations to look for a template in.
	 */
	function template_stack() {

		// define paths
		$template_dir = get_stylesheet_directory();
		$parent_template_dir = get_template_directory();
		$plugin_template_directory = CIVICRM_DIRECTORY_PATH . 'assets/templates/theme';

		// construct stack
		$stack = array( $template_dir, $parent_template_dir, $plugin_template_directory );

		/**
		 * Allow stack to be filtered.
		 *
		 * @since 0.1
		 *
		 * @param array $stack The default template stack.
		 * @return array $stack The filtered template stack.
		 */
		$stack = apply_filters( 'civicrm_directory_template_stack', $stack );

		// sanity check
		$stack = array_unique( $stack );

		// --<
		return $stack;

	}



} // class ends



/**
 * Render the listing section for a directory.
 *
 * @since 0.1.1
 */
function civicrm_directory_listing() {

	// render browse section now
	civicrm_directory()->template->insert_markup();

}



