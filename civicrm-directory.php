<?php
/**
 * Plugin Name: CiviCRM Directory
 * Plugin URI: https://github.com/christianwach/civicrm-directory
 * Description: Creates a publicly-viewable directory from data submitted to CiviCRM.
 * Author: Christian Wach
 * Version: 0.2.8
 * Author URI: http://haystack.co.uk
 * Text Domain: civicrm-directory
 * Domain Path: /languages
 * Depends: CiviCRM
 *
 * @package CiviCRM_Directory
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Set our version here.
define( 'CIVICRM_DIRECTORY_VERSION', '0.2.8' );

// Trigger logging of 'civicrm_pre' and 'civicrm_post'.
if ( ! defined( 'CIVICRM_DIRECTORY_DEBUG' ) ) {
	define( 'CIVICRM_DIRECTORY_DEBUG', false );
}

// Store reference to this file.
if ( ! defined( 'CIVICRM_DIRECTORY_FILE' ) ) {
	define( 'CIVICRM_DIRECTORY_FILE', __FILE__ );
}

// Store URL to this plugin's directory.
if ( ! defined( 'CIVICRM_DIRECTORY_URL' ) ) {
	define( 'CIVICRM_DIRECTORY_URL', plugin_dir_url( CIVICRM_DIRECTORY_FILE ) );
}

// Store PATH to this plugin's directory.
if ( ! defined( 'CIVICRM_DIRECTORY_PATH' ) ) {
	define( 'CIVICRM_DIRECTORY_PATH', plugin_dir_path( CIVICRM_DIRECTORY_FILE ) );
}

/**
 * CiviCRM Directory Class.
 *
 * A class that encapsulates plugin functionality.
 *
 * @since 0.1
 */
class CiviCRM_Directory {

	/**
	 * Admin object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $admin The Admin object.
	 */
	public $admin;

	/**
	 * CiviCRM object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $civi The CiviCRM object.
	 */
	public $civi;

	/**
	 * Custom Post Type object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $cpt The Custom Post Type object.
	 */
	public $cpt;

	/**
	 * Custom Post Type Meta object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $cpt_meta The Custom Post Type Meta object.
	 */
	public $cpt_meta;

	/**
	 * Template object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $template The Template object.
	 */
	public $template;

	/**
	 * Map object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $map The Map object.
	 */
	public $map;

	/**
	 * Browse object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $browse The Browse object.
	 */
	public $browse;

	/**
	 * Search object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $search The Search object.
	 */
	public $search;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Initialise.
		$this->initialise();

		// Use translation files.
		add_action( 'plugins_loaded', [ $this, 'enable_translation' ] );

		// Set up objects when all plugins are loaded.
		add_action( 'plugins_loaded', [ $this, 'setup_objects' ], 20 );

	}

	/**
	 * Do stuff on plugin activation.
	 *
	 * @since 0.1
	 */
	public function activate() {

		// Set up objects.
		$this->setup_objects();

		// Pass to classes that need activation.
		$this->admin->activate();
		$this->cpt->activate();

	}

	/**
	 * Do stuff on plugin deactivation.
	 *
	 * @since 0.1
	 */
	public function deactivate() {

		// Pass to classes that need deactivation.
		$this->cpt->deactivate();

	}

	/**
	 * Do stuff on plugin init.
	 *
	 * @since 0.1
	 */
	public function initialise() {

		// Include files.
		$this->include_files();

		// Add actions and filters.
		$this->register_hooks();

	}

	/**
	 * Include files.
	 *
	 * @since 0.1
	 */
	public function include_files() {

		// Load our functions.
		require CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-functions.php';

		// Load our classes.
		require CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-civi.php';
		require CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-admin.php';
		require CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-cpt.php';
		require CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-cpt-meta.php';
		require CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-template.php';
		require CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-map.php';
		require CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-browse.php';
		require CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-search.php';

	}

	/**
	 * Set up this plugin's objects.
	 *
	 * @since 0.1
	 */
	public function setup_objects() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && $done === true ) {
			return;
		}

		// Init objects.
		$this->admin = new CiviCRM_Directory_Admin( $this );
		$this->admin->register_hooks();

		// CiviCRM class needs no hooks registered.
		$this->civi = new CiviCRM_Directory_Civi( $this );

		$this->cpt = new CiviCRM_Directory_CPT( $this );
		$this->cpt->register_hooks();

		$this->cpt_meta = new CiviCRM_Directory_CPT_Meta( $this );
		$this->cpt_meta->register_hooks();

		$this->template = new CiviCRM_Directory_Template( $this );
		$this->template->register_hooks();

		// Map class needs no hooks.
		$this->map = new CiviCRM_Directory_Map( $this );

		$this->browse = new CiviCRM_Directory_Browse( $this );
		$this->browse->register_hooks();

		$this->search = new CiviCRM_Directory_Search( $this );
		$this->search->register_hooks();

		// We're done.
		$done = true;

	}

	/**
	 * Load translation files.
	 *
	 * @since 0.1
	 */
	public function enable_translation() {

		// Load translations.
		// phpcs:ignore WordPress.WP.DeprecatedParameters.Load_plugin_textdomainParam2Found
		load_plugin_textdomain(
			'civicrm-directory', // Unique name.
			false, // Deprecated argument.
			dirname( plugin_basename( __FILE__ ) ) . '/languages/' // Relative path.
		);

	}

	//##########################################################################

	/**
	 * Register hooks.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// Bail if CiviCRM is not present.
		if ( ! function_exists( 'civi_wp' ) ) {
			return;
		}

	}

}

/**
 * Utility to get a reference to this plugin.
 *
 * @since 0.1
 *
 * @return object $civicrm_directory The plugin reference.
 */
function civicrm_directory() {

	// Return instance.
	global $civicrm_directory;
	if ( ! isset( $civicrm_directory ) ) {
		$civicrm_directory = new CiviCRM_Directory();
	}
	return $civicrm_directory;

}

// Init plugin.
civicrm_directory();

// Activation.
register_activation_hook( __FILE__, [ civicrm_directory(), 'activate' ] );

// Deactivation.
register_deactivation_hook( __FILE__, [ civicrm_directory(), 'deactivate' ] );

/*
 * Uninstall uses the 'uninstall.php' method.
 * @see https://developer.wordpress.org/reference/functions/register_uninstall_hook/
 */

/**
 * Utility to add link to settings page.
 *
 * @since 0.2.6
 *
 * @param array $links The existing links array.
 * @param str $file The name of the plugin file.
 * @return array $links The modified links array.
 */
function civicrm_directory_plugin_action_links( $links, $file ) {

	// Bail if CiviCRM plugin is not present.
	if ( ! function_exists( 'civi_wp' ) ) {
		return $links;
	}

	// Add settings link.
	if ( $file == plugin_basename( dirname( __FILE__ ) . '/civicrm-directory.php' ) ) {

		// Is this Network Admin?
		$link = add_query_arg( [ 'page' => 'civicrm_directory_settings' ], admin_url( 'options-general.php' ) );

		// Add settings link.
		$links[] = '<a href="' . esc_url( $link ) . '">' . esc_html__( 'Settings', 'civicrm-directory' ) . '</a>';

	}

	// --<
	return $links;

}

// Add filters for the above.
add_filter( 'network_admin_plugin_action_links', 'civicrm_directory_plugin_action_links', 10, 2 );
add_filter( 'plugin_action_links', 'civicrm_directory_plugin_action_links', 10, 2 );
