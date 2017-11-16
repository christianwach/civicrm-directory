<?php

/**
 * CiviCRM Directory Admin Class.
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
	public $settings = array();



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

		// load settings
		$this->settings = $this->settings_get();

		// load plugin version
		$this->plugin_version = $this->version_get();

		// perform any upgrade tasks
		$this->upgrade_tasks();

	}



	/**
	 * Perform activation tasks.
	 *
	 * @since 0.1
	 */
	public function activate() {

		// store plugin version
		$this->version_set();

		// add settings option
		$this->settings_init();

	}



	/**
	 * Perform upgrade tasks.
	 *
	 * @since 0.2.3
	 */
	public function upgrade_tasks() {

		// bail if no upgrade is needed
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

		// flush rules late
		add_action( 'init', 'flush_rewrite_rules', 100 );

		// if the current version is less than 0.2.5 and we're upgrading to 0.2.5+
		if (
			version_compare( $this->plugin_version, '0.2.5', '<' ) AND
			version_compare( CIVICRM_DIRECTORY_VERSION, '0.2.5', '>=' )
		) {

			// get current default Group ID
			$civicrm_group_ids = array_keys( $this->setting_get( 'group_ids', array( 0 => 0 ) ) );
			$group_id = $civicrm_group_ids[0];

			// store as single integer
			$this->setting_set( 'group_id', $group_id );

			// remove old setting
			$this->setting_unset( 'group_ids' );

			// save settings
			$this->settings_save();

		}

		// if the current version is less than 0.2.6 and we're upgrading to 0.2.6+
		if (
			version_compare( $this->plugin_version, '0.2.6', '<' ) AND
			version_compare( CIVICRM_DIRECTORY_VERSION, '0.2.6', '>=' )
		) {

			// remove old settings
			$this->setting_unset( 'group_id' );
			$this->setting_unset( 'longitude' );
			$this->setting_unset( 'latitude' );
			$this->setting_unset( 'zoom' );

			// save settings
			$this->settings_save();

		}

		// if the current version is less than 0.2.8 and we're upgrading to 0.2.8+
		if (
			version_compare( $this->plugin_version, '0.2.8', '<' ) AND
			version_compare( CIVICRM_DIRECTORY_VERSION, '0.2.8', '>=' )
		) {

			// get default Map Height
			$defaults = $this->settings_get_default();
			$height = $defaults['google_maps_height'];

			// store setting
			$this->setting_set( 'google_maps_height', $height );

			// save settings
			$this->settings_save();

		}

		// store new version
		$this->version_set();

	}



	/**
	 * Register hooks on plugin init.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// add menu item
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

	}



	//##########################################################################



	/**
	 * Add this plugin's Settings Page to the WordPress admin menu.
	 *
	 * @since 0.1
	 */
	public function admin_menu() {

		// check user permissions
		if ( ! current_user_can('manage_options') ) return false;

		// add the General Settings page to the Settings menu
		$this->settings_page = add_options_page(
			__( 'CiviCRM Directory: Settings', 'civicrm-directory' ), // page title
			__( 'CiviCRM Directory', 'civicrm-directory' ), // menu title
			'manage_options', // required caps
			'civicrm_directory_settings', // slug name
			array( $this, 'page_settings' ) // callback
		);

		// maybe save settings on page load
		add_action( 'load-' . $this->settings_page, array( $this, 'settings_general_parse' ) );

		// add help text to UI
		add_action( 'admin_head-' . $this->settings_page, array( $this, 'admin_head' ) );

		/*
		// add scripts and styles
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

		// get screen object
		$screen = get_current_screen();

		// pass to help method
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

		// kick out if not our screen
		if ( $screen->id != $this->settings_page ) {
			return $screen;
		}

		// add a help tab
		$screen->add_help_tab( array(
			'id' => 'civicrm_directory_help',
			'title' => __( 'CiviCRM Directory', 'civicrm-directory' ),
			'content' => $this->admin_help_text(),
		));

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

		// stub help text, to be developed further
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

		// store version
		update_option( 'civicrm_directory_version', CIVICRM_DIRECTORY_VERSION );

	}



	/**
	 * Get the current plugin version.
	 *
	 * @since 0.1
	 */
	public function version_get() {

		// retrieve version
		return get_option( 'civicrm_directory_version', CIVICRM_DIRECTORY_VERSION );

	}



	//##########################################################################



	/**
	 * Show General Settings page.
	 *
	 * @since 0.1
	 */
	public function page_settings() {

		// check user permissions
		if ( ! current_user_can( 'manage_options' ) ) return;

		// get admin page URL
		$url = $this->page_get_url();

		// Google Maps API key
		$google_maps_key = $this->setting_get( 'google_maps_key' );

		// Google Maps Height
		$google_maps_height = $this->setting_get( 'google_maps_height' );

		// include template file
		include( CIVICRM_DIRECTORY_PATH . 'assets/templates/admin/settings-general.php' );

	}



	/**
	 * Get admin page URL.
	 *
	 * @since 0.2.5
	 *
	 * @return array $admin_url The admin page URL.
	 */
	public function page_get_url() {

		// only calculate once
		if ( isset( $this->url ) ) {
			return $this->url;
		}

		// construct admin page URL
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

		// add settings option if it does not exist
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

		// bail if no post data
		if ( empty( $_POST ) ) return;

		// check that we trust the source of the request
		check_admin_referer( 'civicrm_directory_settings_action', 'civicrm_directory_nonce' );

		// check that our sumbit button was clicked
		if ( ! isset( $_POST['civicrm_directory_settings_submit'] ) ) return;

		// okay, now update
		$this->settings_general_update();

	}



	/**
	 * Update General Settings.
	 *
	 * @since 0.1
	 */
	public function settings_general_update() {

		// Google Maps API key
		$google_maps_key = $this->setting_get( 'google_maps_key' );
		if ( isset( $_POST['civicrm_directory_google_maps_key'] ) ) {
			$google_maps_key = trim( $_POST['civicrm_directory_google_maps_key'] );
		}
		$this->setting_set( 'google_maps_key', $google_maps_key );

		// Google Maps Height
		$google_maps_height = $this->setting_get( 'google_maps_height' );
		if ( isset( $_POST['civicrm_directory_google_maps_height'] ) ) {
			$google_maps_height = absint( trim( $_POST['civicrm_directory_google_maps_height'] ) );
		}

		// substitute with default if empty
		if ( empty( $google_maps_height ) OR $google_maps_height === 0 ) {
			$defaults = $this->settings_get_default();
			$google_maps_height = $defaults['google_maps_height'];
		}

		$this->setting_set( 'google_maps_height', $google_maps_height );

		// save settings
		$this->settings_save();

		// construct General Settings page URL
		$url = $this->page_get_url();
		$redirect = add_query_arg( 'updated', 'true', $url );

		// prevent reload weirdness
		wp_redirect( $redirect );

	}



	/**
	 * Get current plugin settings.
	 *
	 * @since 0.1
	 *
	 * @return array $settings The array of settings, keyed by setting name.
	 */
	public function settings_get() {

		// get settings option
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

		// update settings option
		update_option( 'civicrm_directory_settings', $settings );

	}



	/**
	 * Save plugin settings.
	 *
	 * @since 0.1
	 */
	public function settings_save() {

		// sanity check
		if ( empty( $this->settings ) ) return;

		// save current state of settings array
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

		// init return
		$settings = array();

		// default Google Maps key (empty)
		$settings['google_maps_key'] = '';

		// default Google Maps Height in px (400)
		$settings['google_maps_height'] = 400;

		/**
		 * Allow defaults to be filtered.
		 *
		 * @since 0.1
		 *
		 * @param array $settings The default settings array
		 * @return array $settings The modified settings array
		 */
		return apply_filters( 'civicrm_directory_default_settings', $settings );

	}



	/**
	 * Return a value for a specified setting.
	 *
	 * @since 0.1
	 *
	 * @param str $setting_name The name of the setting
	 * @return mixed $default The default value of the setting
	 * @return mixed $setting The actual value of the setting
	 */
	public function setting_get( $setting_name = '', $default = false ) {

		// get setting
		return ( array_key_exists( $setting_name, $this->settings ) ) ? $this->settings[$setting_name] : $default;

	}



	/**
	 * Set a value for a specified setting.
	 *
	 * @since 0.1
	 */
	public function setting_set( $setting_name = '', $value = '' ) {

		// set setting
		$this->settings[$setting_name] = $value;

	}



	/**
	 * Unset a specified setting.
	 *
	 * @since 0.2.4
	 */
	public function setting_unset( $setting_name = '' ) {

		// set setting
		unset( $this->settings[$setting_name] );

	}



} // class ends



