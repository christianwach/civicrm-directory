<?php /*
--------------------------------------------------------------------------------
Plugin Name: CiviCRM Directory
Plugin URI: https://github.com/christianwach/civicrm-directory
Description: Creates a publicly-viewable directory from data submitted to CiviCRM.
Author: Christian Wach
Version: 0.2.8
Author URI: http://haystack.co.uk
Text Domain: civicrm-directory
Domain Path: /languages
Depends: CiviCRM
--------------------------------------------------------------------------------
*/



// set our version here
define( 'CIVICRM_DIRECTORY_VERSION', '0.2.8' );

// trigger logging of 'civicrm_pre' and 'civicrm_post'
if ( ! defined( 'CIVICRM_DIRECTORY_DEBUG' ) ) {
	define( 'CIVICRM_DIRECTORY_DEBUG', false );
}

// store reference to this file
if ( ! defined( 'CIVICRM_DIRECTORY_FILE' ) ) {
	define( 'CIVICRM_DIRECTORY_FILE', __FILE__ );
}

// store URL to this plugin's directory
if ( ! defined( 'CIVICRM_DIRECTORY_URL' ) ) {
	define( 'CIVICRM_DIRECTORY_URL', plugin_dir_url( CIVICRM_DIRECTORY_FILE ) );
}

// store PATH to this plugin's directory
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

		// initialise
		$this->initialise();

		// use translation files
		add_action( 'plugins_loaded', array( $this, 'enable_translation' ) );

		// set up objects when all plugins are loaded
		add_action( 'plugins_loaded', array( $this, 'setup_objects' ), 20 );

	}



	/**
	 * Do stuff on plugin activation.
	 *
	 * @since 0.1
	 */
	public function activate() {

		// set up objects
		$this->setup_objects();

		// pass to classes that need activation
		$this->admin->activate();
		$this->cpt->activate();

	}



	/**
	 * Do stuff on plugin deactivation.
	 *
	 * @since 0.1
	 */
	public function deactivate() {

		// pass to classes that need deactivation
		$this->cpt->deactivate();

	}



	/**
	 * Do stuff on plugin init.
	 *
	 * @since 0.1
	 */
	public function initialise() {

		// include files
		$this->include_files();

		// add actions and filters
		$this->register_hooks();

	}



	/**
	 * Include files.
	 *
	 * @since 0.1
	 */
	public function include_files() {

		// load our CiviCRM class
		require( CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-civi.php' );

		// load our Admin class
		require( CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-admin.php' );

		// load our CPT class
		require( CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-cpt.php' );

		// load our CPT Meta class
		require( CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-cpt-meta.php' );

		// load our Template class
		require( CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-template.php' );

		// load our Map class
		require( CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-map.php' );

		// load our Browse class
		require( CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-browse.php' );

		// load our Search class
		require( CIVICRM_DIRECTORY_PATH . 'includes/civicrm-directory-search.php' );

	}



	/**
	 * Set up this plugin's objects.
	 *
	 * @since 0.1
	 */
	public function setup_objects() {

		// init flag
		static $done;

		// only do this once
		if ( isset( $done ) AND $done === true ) return;

		// init objects
		$this->admin = new CiviCRM_Directory_Admin( $this );
		$this->admin->register_hooks();

		$this->civi = new CiviCRM_Directory_Civi( $this );
		// CiviCRM class needs no hooks

		$this->cpt = new CiviCRM_Directory_CPT( $this );
		$this->cpt->register_hooks();

		$this->cpt_meta = new CiviCRM_Directory_CPT_Meta( $this );
		$this->cpt_meta->register_hooks();

		$this->template = new CiviCRM_Directory_Template( $this );
		$this->template->register_hooks();

		$this->map = new CiviCRM_Directory_Map( $this );
		// map class needs no hooks

		$this->browse = new CiviCRM_Directory_Browse( $this );
		$this->browse->register_hooks();

		$this->search = new CiviCRM_Directory_Search( $this );
		$this->search->register_hooks();

		// we're done
		$done = true;

	}



	/**
	 * Load translation files.
	 *
	 * @since 0.1
	 */
	public function enable_translation() {

		// load translations
		load_plugin_textdomain(
			'civicrm-directory', // unique name
			false, // deprecated argument
			dirname( plugin_basename( __FILE__ ) ) . '/languages/' // relative path
		);

	}



	//##########################################################################



	/**
	 * Register hooks.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// bail if CiviCRM is not present
		if ( ! function_exists( 'civi_wp' ) ) return;

	}



} // class ends



// init plugin
global $civicrm_directory;
$civicrm_directory = new CiviCRM_Directory;

// activation
register_activation_hook( __FILE__, array( $civicrm_directory, 'activate' ) );

// deactivation
register_deactivation_hook( __FILE__, array( $civicrm_directory, 'deactivate' ) );

// uninstall will use the 'uninstall.php' method when fully built
// see: http://codex.wordpress.org/Function_Reference/register_uninstall_hook



/**
 * Utility to get a reference to this plugin.
 *
 * @since 0.1
 *
 * @return object $civicrm_directory The plugin reference.
 */
function civicrm_directory() {

	// return instance
	global $civicrm_directory;
	return $civicrm_directory;

}



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

	// bail if CiviCRM plugin is not present
	if ( ! function_exists( 'civi_wp' ) ) return $links;

	// add settings link
	if ( $file == plugin_basename( dirname( __FILE__ ) . '/civicrm-directory.php' ) ) {

		// is this Network Admin?
		$link = add_query_arg( array( 'page' => 'civicrm_directory_settings' ), admin_url( 'options-general.php' ) );

		// add settings link
		$links[] = '<a href="' . esc_url( $link ) . '">' . esc_html__( 'Settings', 'civicrm-directory' ) . '</a>';

	}

	// --<
	return $links;

}

// add filters for the above
add_filter( 'network_admin_plugin_action_links', 'civicrm_directory_plugin_action_links', 10, 2 );
add_filter( 'plugin_action_links', 'civicrm_directory_plugin_action_links', 10, 2 );



