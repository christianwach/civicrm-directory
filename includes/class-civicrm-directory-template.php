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
	 * Callback filter to display a Directory.
	 *
	 * @param str $content The existing content.
	 * @return str $content The modified content.
	 */
	function directory_render( $content ) {

		// only on canonical Directory pages
		if ( ! is_singular( $this->plugin->cpt->post_type_name ) ) {
			return $content;
		}

		// only for our post type
		if ( get_post_type( get_the_ID() ) !== $this->plugin->cpt->post_type_name ) {
			return $content;
		}

		// get template
		$template = $this->find_file( 'civicrm-directory/directory-index.php' );

		// buffer the template part
		ob_start();
		include( $template );
		$content = ob_get_contents();
		ob_end_clean();

		// --<
		return $content;

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



} // class CiviCRM_Directory_CPT ends



