<?php
/**
 * Custom Post Type Class.
 *
 * Handles registration of a Custom Post Type for CiviCRM Directory.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Custom Post Type Class.
 *
 * A class that encapsulates a Custom Post Type for CiviCRM Directory. This is
 * used to provide multiple Directories per WordPress install, each of which has
 * a unique permalink and ID.
 *
 * @since 0.1
 */
class CiviCRM_Directory_CPT {

	/**
	 * Plugin (calling) object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $plugin The plugin object.
	 */
	public $plugin;

	/**
	 * Custom Post Type name.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $cpt The name of the Custom Post Type.
	 */
	public $post_type_name = 'directory';

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

		// Always register post type.
		add_action( 'init', [ $this, 'post_type_create' ] );

		// Make sure our feedback is appropriate.
		add_filter( 'post_updated_messages', [ $this, 'post_type_messages' ] );

		// Custom endpoints.
		add_action( 'init', [ $this, 'endpoints' ], 20 );

	}

	/**
	 * Actions to perform on plugin activation.
	 *
	 * @since 0.1
	 */
	public function activate() {

		// Pass through.
		$this->post_type_create();
		$this->endpoints();

		// Go ahead and flush.
		flush_rewrite_rules();

	}

	/**
	 * Actions to perform on plugin deactivation (NOT deletion).
	 *
	 * @since 0.1
	 */
	public function deactivate() {

		// Flush rules to reset.
		flush_rewrite_rules();

	}

	// #########################################################################

	/**
	 * Create our Custom Post Type.
	 *
	 * @since 0.1
	 */
	public function post_type_create() {

		// Only call this once.
		static $registered;
		if ( $registered ) {
			return;
		}

		// Set up the post type called "Directory".
		register_post_type( $this->post_type_name, [

			// Labels.
			'labels' => [
				'name'               => __( 'Directories', 'civicrm-directory' ),
				'singular_name'      => __( 'Directory', 'civicrm-directory' ),
				'add_new'            => __( 'Add New', 'civicrm-directory' ),
				'add_new_item'       => __( 'Add New Directory', 'civicrm-directory' ),
				'edit_item'          => __( 'Edit Directory', 'civicrm-directory' ),
				'new_item'           => __( 'New Directory', 'civicrm-directory' ),
				'all_items'          => __( 'All Directories', 'civicrm-directory' ),
				'view_item'          => __( 'View Directory', 'civicrm-directory' ),
				'search_items'       => __( 'Search Directories', 'civicrm-directory' ),
				'not_found'          => __( 'No matching Directory found', 'civicrm-directory' ),
				'not_found_in_trash' => __( 'No Directories found in Trash', 'civicrm-directory' ),
				'menu_name'          => __( 'Directories', 'civicrm-directory' ),
			],

			// Defaults.
			'menu_icon'   => 'dashicons-list-view',
			'description' => __( 'A directory post type', 'civicrm-directory' ),
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'show_in_admin_bar' => true,
			'has_archive' => false,
			'query_var' => true,
			'capability_type' => 'page',
			'hierarchical' => true,
			'menu_position' => 25,
			'map_meta_cap' => true,

			// Rewrite.
			'rewrite' => [
				'slug' => 'directory',
				'with_front' => false,
			],

			// Supports.
			'supports' => [
				'title',
				'page-attributes',
			],

		] );

		// Flag done.
		$registered = true;

	}

	/**
	 * Override messages for a custom post type.
	 *
	 * @since 0.1
	 *
	 * @param array $messages The existing messages.
	 * @return array $messages The modified messages.
	 */
	public function post_type_messages( $messages ) {

		// Access relevant globals.
		global $post, $post_ID;

		// Define custom messages for our custom post type.
		$messages[ $this->post_type_name ] = [

			// Unused - messages start at index 1.
			0 => '',

			// Item updated.
			1 => sprintf(
				/* translators: %s: The URL of the directory */
				__( 'Directory updated. <a href="%s">View directory</a>', 'civicrm-directory' ),
				esc_url( get_permalink( $post_ID ) )
			),

			// Custom fields.
			2 => __( 'Custom field updated.', 'civicrm-directory' ),
			3 => __( 'Custom field deleted.', 'civicrm-directory' ),
			4 => __( 'Directory updated.', 'civicrm-directory' ),

			// Item restored to a revision.
			5 => isset( $_GET['revision'] ) ?

					// Revision text.
					sprintf(
						/* translators: %s: date and time of the revision */
						__( 'Directory restored to revision from %s', 'civicrm-directory' ),
						wp_post_revision_title( (int) $_GET['revision'], false )
					) :

					// No revision.
					false,

			// Item published.
			6 => sprintf(
				/* translators: %s: The URL of the directory */
				__( 'Directory published. <a href="%s">View directory</a>', 'civicrm-directory' ),
				esc_url( get_permalink( $post_ID ) )
			),

			// Item saved.
			7 => __( 'Directory saved.', 'civicrm-directory' ),

			// Item submitted.
			8 => sprintf(
				/* translators: %s: The URL of the directory preview */
				__( 'Directory submitted. <a target="_blank" href="%s">Preview directory</a>', 'civicrm-directory' ),
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) )
			),

			// Item scheduled.
			9 => sprintf(
				/* translators: 1: The date, 2: The URL of the preview */
				__( 'Directory scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview directory</a>', 'civicrm-directory' ),
				/* translators: Publish box date format, see https://php.net/date */
				date_i18n( __( 'M j, Y @ G:i', 'civicrm-directory' ),
				strtotime( $post->post_date ) ),
				esc_url( get_permalink( $post_ID ) )
			),

			// Draft updated.
			10 => sprintf(
				/* translators: %s: The URL of the preview */
				__( 'Directory draft updated. <a target="_blank" href="%s">Preview directory</a>', 'civicrm-directory' ),
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) )
			),

		];

		// --<
		return $messages;

	}

	// #########################################################################

	/**
	 * Add our endpoints.
	 *
	 * @since 0.2.3
	 *
	 * @param bool $flush_rewrite_rules True if rules should be flushed, false otherwise.
	 */
	public function endpoints( $flush_rewrite_rules = false ) {

		// Let's add an endpoint for viewing entries.
		add_rewrite_endpoint( 'entry', EP_PERMALINK | EP_PAGES );

		// Maybe force flush.
		if ( $flush_rewrite_rules ) {
			flush_rewrite_rules();
		}

		/**
		 * Broadcast the rewrite rules event.
		 *
		 * @since 0.2.1
		 *
		 * @param bool $flush_rewrite_rules True if rules flushed, false otherwise.
		 */
		do_action( 'civicrm_directory_after_endpoints', $flush_rewrite_rules );

	}

	/**
	 * Add our query vars.
	 *
	 * @since 0.2.1
	 *
	 * @param array $query_vars The existing array of query vars.
	 * @return array $query_vars The modified array of query vars.
	 */
	public function query_vars( $query_vars ) {

		// Sanity check.
		if ( ! is_array( $query_vars ) ) {
			$query_vars = [];
		}

		// Add our query vars.
		$query_vars[] = 'post_type';
		$query_vars[] = 'pagename';
		$query_vars[] = 'cividir_contact_id';

		// --<
		return $query_vars;

	}

}
