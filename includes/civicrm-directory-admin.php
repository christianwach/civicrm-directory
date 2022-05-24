<?php
/**
 * Admin Class.
 *
 * Handles admin functionality.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Admin Class.
 *
 * A class that encapsulates admin functionality.
 *
 * @since 0.1
 */
class CiviCRM_Directory_Admin {

	/**
	 * Plugin (calling) object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $plugin The plugin object.
	 */
	public $plugin;

	/**
	 * Plugin version.
	 *
	 * @since 0.2.3
	 * @access public
	 * @var str $plugin_version The plugin version.
	 */
	public $plugin_version;

	/**
	 * General Settings page.
	 *
	 * @since 0.2.5
	 * @access public
	 * @var str $settings_page The General Settings page reference.
	 */
	public $settings_page;

	/**
	 * Settings data.
	 *
	 * @since 0.1
	 * @access public
	 * @var array $settings The plugin settings data.
	 */
	public $settings = [];

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

		// Load settings.
		$this->settings = $this->settings_get();

		// Load plugin version.
		$this->plugin_version = $this->version_get();

		// Perform any upgrade tasks.
		$this->upgrade_tasks();

	}

	/**
	 * Perform activation tasks.
	 *
	 * @since 0.1
	 */
	public function activate() {

		// Store plugin version.
		$this->version_set();

		// Add settings option.
		$this->settings_init();

	}

	/**
	 * Perform upgrade tasks.
	 *
	 * @since 0.2.3
	 */
	public function upgrade_tasks() {

		// Bail if no upgrade is needed.
		if ( version_compare( $this->plugin_version, CIVICRM_DIRECTORY_VERSION, '>=' ) ) {
			return;
		}

		/**
		 * Broadcast plugin upgrade.
		 *
		 * @since 0.2.3
		 *
		 * @param str $plugin_version The previous plugin version.
		 * @param str CIVICRM_DIRECTORY_VERSION The current plugin version.
		 */
		do_action( 'civicrm_directory_upgrade', $this->plugin_version, CIVICRM_DIRECTORY_VERSION );

		// Flush rules late.
		add_action( 'init', 'flush_rewrite_rules', 100 );

		// If the current version is less than 0.2.5 and we're upgrading to 0.2.5+.
		if (
			version_compare( $this->plugin_version, '0.2.5', '<' ) &&
			version_compare( CIVICRM_DIRECTORY_VERSION, '0.2.5', '>=' )
		) {

			// Get current default Group ID.
			$civicrm_group_ids = array_keys( $this->setting_get( 'group_ids', [ 0 => 0 ] ) );
			$group_id = $civicrm_group_ids[0];

			// Store as single integer.
			$this->setting_set( 'group_id', $group_id );

			// Remove old setting.
			$this->setting_unset( 'group_ids' );

			// Save settings.
			$this->settings_save();

		}

		// If the current version is less than 0.2.6 and we're upgrading to 0.2.6+.
		if (
			version_compare( $this->plugin_version, '0.2.6', '<' ) &&
			version_compare( CIVICRM_DIRECTORY_VERSION, '0.2.6', '>=' )
		) {

			// Remove old settings.
			$this->setting_unset( 'group_id' );
			$this->setting_unset( 'longitude' );
			$this->setting_unset( 'latitude' );
			$this->setting_unset( 'zoom' );

			// Save settings.
			$this->settings_save();

		}

		// If the current version is less than 0.2.8 and we're upgrading to 0.2.8+.
		if (
			version_compare( $this->plugin_version, '0.2.8', '<' ) &&
			version_compare( CIVICRM_DIRECTORY_VERSION, '0.2.8', '>=' )
		) {

			// Get default Map Height.
			$defaults = $this->settings_get_default();
			$height = $defaults['google_maps_height'];

			// Store setting.
			$this->setting_set( 'google_maps_height', $height );

			// Save settings.
			$this->settings_save();

		}

		// Store new version.
		$this->version_set();

	}

	/**
	 * Register hooks on plugin init.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// Add menu item.
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

	}

	//##########################################################################

	/**
	 * Add this plugin's Settings Page to the WordPress admin menu.
	 *
	 * @since 0.1
	 */
	public function admin_menu() {

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Add the General Settings page to the Settings menu.
		$this->settings_page = add_options_page(
			__( 'CiviCRM Directory: Settings', 'civicrm-directory' ), // Page title.
			__( 'CiviCRM Directory', 'civicrm-directory' ), // Menu title.
			'manage_options', // Required caps.
			'civicrm_directory_settings', // Slug name.
			[ $this, 'page_settings' ] // Callback.
		);

		// Maybe save settings on page load.
		add_action( 'load-' . $this->settings_page, [ $this, 'settings_general_parse' ] );

		// Add help text to UI.
		add_action( 'admin_head-' . $this->settings_page, [ $this, 'admin_head' ] );

		/*
		// Add scripts and styles.
		add_action( 'admin_print_scripts-' . $this->settings_page, array( $this, 'admin_js' ) );
		add_action( 'admin_print_styles-' . $this->settings_page, array( $this, 'admin_css' ) );
		*/

	}

	/**
	 * Initialise plugin help.
	 *
	 * @since 0.1
	 */
	public function admin_head() {

		// Get screen object.
		$screen = get_current_screen();

		// Pass to help method.
		$this->admin_help( $screen );

	}

	/**
	 * Adds help copy to our admin page.
	 *
	 * @since 0.1
	 *
	 * @param object $screen The existing WordPress screen object.
	 * @return object $screen The amended WordPress screen object.
	 */
	public function admin_help( $screen ) {

		// Kick out if not our screen.
		if ( $screen->id != $this->settings_page ) {
			return $screen;
		}

		// Add a help tab.
		$screen->add_help_tab( [
			'id' => 'civicrm_directory_help',
			'title' => __( 'CiviCRM Directory', 'civicrm-directory' ),
			'content' => $this->admin_help_text(),
		]);

		// --<
		return $screen;

	}

	/**
	 * Get HTML-formatted help text for the admin screen.
	 *
	 * @since 0.1
	 *
	 * @return string $help The help text formatted as HTML.
	 */
	public function admin_help_text() {

		// Stub help text, to be developed further.
		$help = '<p>' . __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vel iaculis leo. Fusce eget erat vitae justo vestibulum tincidunt efficitur id nunc. Vivamus id quam tempus, aliquam tortor nec, volutpat nisl. Ut venenatis aliquam enim, a placerat libero vehicula quis. Etiam neque risus, vestibulum facilisis erat a, tincidunt vestibulum nulla. Sed ultrices ante nulla. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Praesent maximus purus ac lacinia vulputate. Aenean ex quam, aliquet id feugiat et, cursus vel magna. Cras id congue ipsum, vel consequat libero.', 'civicrm-directory' ) . '</p>';

		// --<
		return $help;

	}

	//##########################################################################

	/**
	 * Store the plugin version.
	 *
	 * @since 0.1
	 */
	public function version_set() {

		// Store version.
		update_option( 'civicrm_directory_version', CIVICRM_DIRECTORY_VERSION );

	}

	/**
	 * Get the current plugin version.
	 *
	 * @since 0.1
	 */
	public function version_get() {

		// Retrieve version.
		return get_option( 'civicrm_directory_version', CIVICRM_DIRECTORY_VERSION );

	}

	//##########################################################################

	/**
	 * Show General Settings page.
	 *
	 * @since 0.1
	 */
	public function page_settings() {

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Get admin page URL.
		$url = $this->page_get_url();

		// Google Maps API key.
		$google_maps_key = $this->setting_get( 'google_maps_key' );

		// Google Maps Height.
		$google_maps_height = $this->setting_get( 'google_maps_height' );

		// Include template file.
		include CIVICRM_DIRECTORY_PATH . 'assets/templates/admin/settings-general.php';

	}

	/**
	 * Get admin page URL.
	 *
	 * @since 0.2.5
	 *
	 * @return array $admin_url The admin page URL.
	 */
	public function page_get_url() {

		// Only calculate once.
		if ( isset( $this->url ) ) {
			return $this->url;
		}

		// Construct admin page URL.
		$this->url = menu_page_url( 'civicrm_directory_settings', false );

		// --<
		return $this->url;

	}

	//##########################################################################

	/**
	 * Initialise plugin settings.
	 *
	 * @since 0.1
	 */
	public function settings_init() {

		// Add settings option if it does not exist.
		if ( 'fgffgs' == get_option( 'civicrm_directory_settings', 'fgffgs' ) ) {
			add_option( 'civicrm_directory_settings', $this->settings_get_default() );
		}

	}

	/**
	 * Maybe save general settings.
	 *
	 * This is the callback from 'load-' . $this->settings_page which determines
	 * if there are settings to be saved and parses them before calling the
	 * actual save method.
	 *
	 * @since 0.1
	 */
	public function settings_general_parse() {

		// Bail if no post data.
		if ( empty( $_POST ) ) {
			return;
		}

		// Check that we trust the source of the request.
		check_admin_referer( 'civicrm_directory_settings_action', 'civicrm_directory_nonce' );

		// Check that our sumbit button was clicked.
		if ( ! isset( $_POST['civicrm_directory_settings_submit'] ) ) {
			return;
		}

		// Okay, now update.
		$this->settings_general_update();

	}

	/**
	 * Update General Settings.
	 *
	 * @since 0.1
	 */
	public function settings_general_update() {

		// Google Maps API key.
		$google_maps_key = $this->setting_get( 'google_maps_key' );
		if ( isset( $_POST['civicrm_directory_google_maps_key'] ) ) {
			$google_maps_key = trim( $_POST['civicrm_directory_google_maps_key'] );
		}
		$this->setting_set( 'google_maps_key', $google_maps_key );

		// Google Maps Height.
		$google_maps_height = $this->setting_get( 'google_maps_height' );
		if ( isset( $_POST['civicrm_directory_google_maps_height'] ) ) {
			$google_maps_height = absint( trim( $_POST['civicrm_directory_google_maps_height'] ) );
		}

		// Substitute with default if empty.
		if ( empty( $google_maps_height ) || $google_maps_height === 0 ) {
			$defaults = $this->settings_get_default();
			$google_maps_height = $defaults['google_maps_height'];
		}

		$this->setting_set( 'google_maps_height', $google_maps_height );

		// Save settings.
		$this->settings_save();

		// Construct General Settings page URL.
		$url = $this->page_get_url();
		$redirect = add_query_arg( 'updated', 'true', $url );

		// Prevent reload weirdness.
		wp_safe_redirect( $redirect );
		exit();

	}

	/**
	 * Get current plugin settings.
	 *
	 * @since 0.1
	 *
	 * @return array $settings The array of settings, keyed by setting name.
	 */
	public function settings_get() {

		// Get settings option.
		return get_option( 'civicrm_directory_settings', $this->settings_get_default() );

	}

	/**
	 * Store plugin settings.
	 *
	 * @since 0.1
	 *
	 * @param array $settings The array of settings, keyed by setting name.
	 */
	public function settings_set( $settings ) {

		// Update settings option.
		update_option( 'civicrm_directory_settings', $settings );

	}

	/**
	 * Save plugin settings.
	 *
	 * @since 0.1
	 */
	public function settings_save() {

		// Sanity check.
		if ( empty( $this->settings ) ) {
			return;
		}

		// Save current state of settings array.
		$this->settings_set( $this->settings );

	}

	/**
	 * Get default plugin settings.
	 *
	 * @since 0.1
	 *
	 * @return array $settings The array of settings, keyed by setting name.
	 */
	public function settings_get_default() {

		// Init return.
		$settings = [];

		// Default Google Maps key (empty).
		$settings['google_maps_key'] = '';

		// Default Google Maps Height in px (400).
		$settings['google_maps_height'] = 400;

		/**
		 * Allow defaults to be filtered.
		 *
		 * @since 0.1
		 *
		 * @param array $settings The default settings array.
		 * @return array $settings The modified settings array.
		 */
		return apply_filters( 'civicrm_directory_default_settings', $settings );

	}

	/**
	 * Return a value for a specified setting.
	 *
	 * @since 0.1
	 *
	 * @param str $setting_name The name of the setting.
	 * @param mixed $default The default value of the setting.
	 * @return mixed $setting The actual value of the setting.
	 */
	public function setting_get( $setting_name = '', $default = false ) {

		// Get setting.
		return ( array_key_exists( $setting_name, $this->settings ) ) ? $this->settings[ $setting_name ] : $default;

	}

	/**
	 * Set a value for a specified setting.
	 *
	 * @since 0.1
	 *
	 * @param str $setting_name The name of the setting.
	 * @param mixed $value The value to save.
	 */
	public function setting_set( $setting_name = '', $value = '' ) {

		// Set setting.
		$this->settings[ $setting_name ] = $value;

	}

	/**
	 * Unset a specified setting.
	 *
	 * @since 0.2.4
	 *
	 * @param str $setting_name The name of the setting.
	 */
	public function setting_unset( $setting_name = '' ) {

		// Set setting.
		unset( $this->settings[ $setting_name ] );

	}

}
