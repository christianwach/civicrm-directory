<?php

/**
 * CiviCRM Directory Custom Post Type Class.
 *
 * A class that encapsulates a Custom Post Type for CiviCRM Directory. This is
 * used to provide multiple Directories per WordPress install, each of which has
 * a unique permalink and ID.
 *
 * @package CiviCRM_Directory
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

		// store
		$this->plugin = $parent;

	}



	/**
	 * Register WordPress hooks.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// always register post type
		add_action( 'init', array( $this, 'post_type_create' ) );

		// make sure our feedback is appropriate
		add_filter( 'post_updated_messages', array( $this, 'post_type_messages' ) );

		// custom rewrite rules
		add_filter( 'init', array( $this, 'rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );

	}



	/**
	 * Actions to perform on plugin activation.
	 *
	 * @since 0.1
	 */
	public function activate() {

		// pass through
		$this->post_type_create();

		// go ahead and flush
		flush_rewrite_rules();

	}



	/**
	 * Actions to perform on plugin deactivation (NOT deletion).
	 *
	 * @since 0.1
	 */
	public function deactivate() {

		// flush rules to reset
		flush_rewrite_rules();

	}



	// #########################################################################



	/**
	 * Create our Custom Post Type.
	 *
	 * @since 0.1
	 */
	public function post_type_create() {

		// only call this once
		static $registered;

		// bail if already done
		if ( $registered ) return;

		// set up the post type called "Directory"
		register_post_type( $this->post_type_name, array(

			// labels
			'labels' => array(
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
			),

			// defaults
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

			// rewrite
			'rewrite' => array(
				'slug' => 'directory',
				'with_front' => false
			),

			// supports
			'supports' => array(
				'title',
				'page-attributes',
			),

		) );

		//flush_rewrite_rules();

		// flag
		$registered = true;

	}



	/**
	 * Override messages for a custom post type.
	 *
	 * @since 0.1
	 *
	 * @param array $messages The existing messages
	 * @return array $messages The modified messages
	 */
	public function post_type_messages( $messages ) {

		// access relevant globals
		global $post, $post_ID;

		// define custom messages for our custom post type
		$messages[$this->post_type_name] = array(

			// unused - messages start at index 1
			0 => '',

			// item updated
			1 => sprintf(
				__( 'Directory updated. <a href="%s">View directory</a>', 'civicrm-directory' ),
				esc_url( get_permalink( $post_ID ) )
			),

			// custom fields
			2 => __( 'Custom field updated.', 'civicrm-directory' ),
			3 => __( 'Custom field deleted.', 'civicrm-directory' ),
			4 => __( 'Directory updated.', 'civicrm-directory' ),

			// item restored to a revision
			5 => isset( $_GET['revision'] ) ?

					// revision text
					sprintf(
						// translators: %s: date and time of the revision
						__( 'Directory restored to revision from %s', 'civicrm-directory' ),
						wp_post_revision_title( (int) $_GET['revision'], false )
					) :

					// no revision
					false,

			// item published
			6 => sprintf(
				__( 'Directory published. <a href="%s">View directory</a>', 'civicrm-directory' ),
				esc_url( get_permalink( $post_ID ) )
			),

			// item saved
			7 => __( 'Directory saved.', 'civicrm-directory' ),

			// item submitted
			8 => sprintf(
				__( 'Directory submitted. <a target="_blank" href="%s">Preview directory</a>', 'civicrm-directory' ),
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) )
			),

			// item scheduled
			9 => sprintf(
				__( 'Directory scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview directory</a>', 'civicrm-directory' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ),
				strtotime( $post->post_date ) ),
				esc_url( get_permalink( $post_ID ) )
			),

			// draft updated
			10 => sprintf(
				__( 'Directory draft updated. <a target="_blank" href="%s">Preview directory</a>', 'civicrm-directory' ),
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) )
			)

		);

		// --<
		return $messages;

	}



	// #########################################################################



	/**
	 * Add our rewrite rules.
	 *
	 * @since 0.2.1
	 *
	 * @param bool $flush_rewrite_rules True if rules should be flushed, false otherwise.
	 */
	public function rewrite_rules( $flush_rewrite_rules = false ) {

		// get our directories
		$directories = get_posts( array( 'post_type' => $this->post_type_name ) );

		// add rewrite rules for each
		foreach( $directories as $key => $directory ) {

			// parse requests for contacts
			add_rewrite_rule(
				'^directory/' . $directory->post_name . '/view/([0-9]+)/?',
				'index.php?post_type=' . $this->post_type_name . '&page_id=' . $directory->ID . '&cividir_contact_id=$matches[1]',
				'top'
			);

		}

		// maybe force flush
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
		do_action( 'civicrm_directory_after_rewrite_rules', $flush_rewrite_rules );

		flush_rewrite_rules();

	}



	/**
	 * Add our query vars.
	 *
	 * @since 0.2.1
	 */
	public function query_vars( $query_vars ) {

		// sanity check
		if ( ! is_array( $query_vars ) ) {
			$query_vars = array();
		}

		// add our query vars
		$query_vars[] = 'cividir_directory_id';
		$query_vars[] = 'cividir_contact_id';

		// --<
		return $query_vars;

	}



} // class CiviCRM_Directory_CPT ends



