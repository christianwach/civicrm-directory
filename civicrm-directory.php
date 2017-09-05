<?php /*
--------------------------------------------------------------------------------
Plugin Name: CiviCRM Directory
Plugin URI: https://github.com/christianwach/civicrm-directory
Description: Creates a publicly-viewable directory from data submitted to CiviCRM.
Author: Christian Wach
Version: 0.1
Author URI: http://haystack.co.uk
Text Domain: civicrm-directory
Domain Path: /languages
Depends: CiviCRM
--------------------------------------------------------------------------------
*/



// set our version here
define( 'CIVICRM_DIRECTORY_VERSION', '0.1' );

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
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// use translation files
		add_action( 'plugins_loaded', array( $this, 'enable_translation' ) );

		// register hooks when all plugins are loaded
		add_action( 'plugins_loaded', array( $this, 'register_civi_hooks' ) );

	}



	/**
	 * Do stuff on plugin activation.
	 *
	 * @since 0.1
	 */
	public function activate() {

		// nothing

	}



	/**
	 * Do stuff on plugin deactivation.
	 *
	 * @since 0.1
	 */
	public function deactivate() {

		// nothing

	}



	/**
	 * Load translation files.
	 *
	 * @since 0.1
	 */
	public function enable_translation() {

		// there are no translations as yet, here for completeness
		load_plugin_textdomain(

			// unique name
			'civicrm-directory',

			// deprecated argument
			false,

			// relative path to directory containing translation files
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'

		);

	}



	//##########################################################################



	/**
	 * Register hooks if CiviCRM is present.
	 *
	 * @since 0.1
	 */
	public function register_civi_hooks() {

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



