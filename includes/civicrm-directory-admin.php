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
	 * Parent Page.
	 *
	 * @since 0.1
	 * @access public
	 * @var str $parent_page The parent page reference.
	 */
	public $parent_page;

	/**
	 * General Settings page.
	 *
	 * @since 0.1
	 * @access public
	 * @var str $settings The General Settings page reference.
	 */
	public $settings_general_page;

	/**
	 * Mapping Settings Page.
	 *
	 * @since 0.1
	 * @access public
	 * @var str $sync_page The Mapping Settings page reference.
	 */
	public $settings_mapping_page;

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
		$this->parent_page = add_options_page(
			__( 'CiviCRM Directory: General Settings', 'civicrm-directory' ), // page title
			__( 'CiviCRM Directory', 'civicrm-directory' ), // menu title
			'manage_options', // required caps
			'civicrm_directory_parent', // slug name
			array( $this, 'page_settings_general' ) // callback
		);

		// add General Settings page
		$this->settings_general_page = add_submenu_page(
			'civicrm_directory_parent', // parent slug
			__( 'CiviCRM Directory: General Settings', 'civicrm-directory' ), // page title
			__( 'General Settings', 'civicrm-directory' ), // menu title
			'manage_options', // required caps
			'civicrm_directory_settings_general', // slug name
			array( $this, 'page_settings_general' ) // callback
		);

		// maybe save settings on page load
		add_action( 'load-' . $this->settings_general_page, array( $this, 'settings_general_parse' ) );

		// add help text to UI
		add_action( 'admin_head-' . $this->settings_general_page, array( $this, 'admin_head' ) );

		// fix menu highlight
		add_action( 'admin_head-' . $this->settings_general_page, array( $this, 'admin_menu_highlight' ), 50 );

		/*
		// add scripts and styles
		add_action( 'admin_print_scripts-' . $this->settings_general_page, array( $this, 'admin_js' ) );
		add_action( 'admin_print_styles-' . $this->settings_general_page, array( $this, 'admin_css' ) );
		*/

		// add Mapping Settings page
		$this->settings_mapping_page = add_submenu_page(
			'civicrm_directory_parent', // parent slug
			__( 'CiviCRM Directory: Mapping Settings', 'civicrm-directory' ), // page title
			__( 'Mapping Settings', 'civicrm-directory' ), // menu title
			'manage_options', // required caps
			'civicrm_directory_settings_mapping', // slug name
			array( $this, 'page_settings_mapping' ) // callback
		);

		// maybe save settings on page load
		add_action( 'load-' . $this->settings_mapping_page, array( $this, 'settings_mapping_parse' ) );

		// add help text to UI
		add_action( 'admin_head-' . $this->settings_mapping_page, array( $this, 'admin_head' ) );

		// fix menu highlight
		add_action( 'admin_head-' . $this->settings_mapping_page, array( $this, 'admin_menu_highlight' ), 50 );

		/*
		// add scripts and styles
		add_action( 'admin_print_scripts-' . $this->settings_mapping_page, array( $this, 'admin_js' ) );
		add_action( 'admin_print_styles-' . $this->settings_mapping_page, array( $this, 'admin_css' ) );
		*/

	}



	/**
	 * Tell WordPress to highlight the plugin's menu item, regardless of which
	 * actual admin screen we are on.
	 *
	 * @since 0.1
	 *
	 * @global string $plugin_page
	 * @global array $submenu
	 */
	public function admin_menu_highlight() {

		global $plugin_page, $submenu_file;

		// define subpages
		$subpages = array(
		 	'civicrm_directory_settings_general',
		 	'civicrm_directory_settings_mapping',
		 );

		// This tweaks the Settings subnav menu to show only one menu item
		if ( in_array( $plugin_page, $subpages ) ) {
			$plugin_page = 'civicrm_directory_parent';
			$submenu_file = 'civicrm_directory_parent';
		}

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

		// init page IDs
		$pages = array(
			$this->settings_general_page,
			$this->settings_mapping_page,
		);

		// kick out if not our screen
		if ( ! in_array( $screen->id, $pages ) ) {
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
	public function page_settings_general() {

		// check user permissions
		if ( ! current_user_can( 'manage_options' ) ) return;

		// get admin page URLs
		$urls = $this->page_get_urls();

		// get CiviCRM groups that could be Directories
		$groups = $this->plugin->civi->groups_get();

		// get the current CiviCRM group ID
		$civicrm_group_ids = array_keys( $this->setting_get( 'group_ids', array( 0 => 0 ) ) );
		$group_id = $civicrm_group_ids[0];

		// include template file
		include( CIVICRM_DIRECTORY_PATH . 'assets/templates/admin/settings-general.php' );

	}



	/**
	 * Show Mapping Settings page.
	 *
	 * @since 0.1
	 */
	public function page_settings_mapping() {

		// check user permissions
		if ( ! current_user_can( 'manage_options' ) ) return;

		// get admin page URLs
		$urls = $this->page_get_urls();

		// Google Maps API key
		$google_maps_key = $this->setting_get( 'google_maps_key' );

		// default map view location
		$latitude = $this->setting_get( 'latitude' );
		$longitude = $this->setting_get( 'longitude' );

		// default zoom
		$zoom = $this->setting_get( 'zoom' );

		// include template file
		include( CIVICRM_DIRECTORY_PATH . 'assets/templates/admin/settings-mapping.php' );

	}



	/**
	 * Get admin page URLs.
	 *
	 * @since 0.1
	 *
	 * @return array $admin_urls The array of admin page URLs.
	 */
	public function page_get_urls() {

		// only calculate once
		if ( isset( $this->urls ) ) {
			return $this->urls;
		}

		// construct admin page URLs
		$this->urls = array(
			'general' => menu_page_url( 'civicrm_directory_settings_general', false ),
			'mapping' => menu_page_url( 'civicrm_directory_settings_mapping', false ),
		);

		// --<
		return $this->urls;

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
	 * This is the callback from 'load-' . $this->settings_general_page which determines
	 * if there are settings to be saved and parses them before calling the
	 * actual save method.
	 *
	 * @since 0.1
	 */
	public function settings_general_parse() {

		// bail if no post data
		if ( empty( $_POST ) ) return;

		// check that we trust the source of the request
		check_admin_referer( 'civicrm_directory_settings_general_action', 'civicrm_directory_nonce' );

		// check that our sumbit button was clicked
		if ( ! isset( $_POST['civicrm_directory_settings_general_submit'] ) ) return;

		// okay, now update
		$this->settings_general_update();

	}



	/**
	 * Update General Settings.
	 *
	 * @since 0.1
	 */
	public function settings_general_update() {

		/*
		 * The groups data is something of a hack right now because I want it to
		 * be expandable in the future. At present there is just one group that
		 * can be chosen as the directory; but in future there will be unlimited
		 * directories. This will be done in combination with a Custom Post Type
		 * that provides a permalink and unique ID.
		 */

		// CiviCRM group IDs
		$group_data = array_keys( $this->setting_get( 'group_ids', array( 0 => 0 ) ) );
		$civicrm_group_id = $group_data[0];
		if ( isset( $_POST['civicrm_directory_civicrm_group_id'] ) ) {
			$civicrm_group_id = absint( trim( $_POST['civicrm_directory_civicrm_group_id'] ) );
		}

		// WordPress post IDs


		// the data array is of the form `array( $civicrm_group_id => $wp_post_id )`
		$this->setting_set( 'group_ids', array( $civicrm_group_id => 0 ) );

		// save settings
		$this->settings_save();

		// construct General Settings page URL
		$urls = $this->page_get_urls();
		$redirect = add_query_arg( 'updated', 'true', $urls['general'] );

		// prevent reload weirdness
		wp_redirect( $redirect );

	}



	/**
	 * Maybe save Mapping Settings.
	 *
	 * This is the callback from 'load-' . $this->settings_general_page which determines
	 * if there are settings to be saved and parses them before calling the
	 * actual save method.
	 *
	 * @since 0.1
	 */
	public function settings_mapping_parse() {

		// bail if no post data
		if ( empty( $_POST ) ) return;

		// check that we trust the source of the request
		check_admin_referer( 'civicrm_directory_settings_mapping_action', 'civicrm_directory_nonce' );

		// check that our sumbit button was clicked
		if ( ! isset( $_POST['civicrm_directory_settings_mapping_submit'] ) ) return;

		// okay, now update
		$this->settings_mapping_update();

	}



	/**
	 * Update Mapping Settings.
	 *
	 * @since 0.1
	 */
	public function settings_mapping_update() {

		// Google Maps API key
		$google_maps_key = $this->setting_get( 'google_maps_key' );
		if ( isset( $_POST['civicrm_directory_google_maps_key'] ) ) {
			$google_maps_key = trim( $_POST['civicrm_directory_google_maps_key'] );
		}
		$this->setting_set( 'google_maps_key', $google_maps_key );

		// default latitude
		$latitude = $this->setting_get( 'latitude' );
		if ( isset( $_POST['civicrm_directory_latitude'] ) AND is_numeric( $_POST['civicrm_directory_latitude'] ) ) {
			$latitude = floatval( trim( $_POST['civicrm_directory_latitude'] ) );
		}
		$this->setting_set( 'latitude', $latitude );

		// default longitude
		$longitude = $this->setting_get( 'longitude' );
		if ( isset( $_POST['civicrm_directory_longitude'] ) AND is_numeric( $_POST['civicrm_directory_longitude'] ) ) {
			$longitude = floatval( trim( $_POST['civicrm_directory_longitude'] ) );
		}
		$this->setting_set( 'longitude', $longitude );

		// default zoom level
		$zoom = $this->setting_get( 'zoom' );
		if ( isset( $_POST['civicrm_directory_zoom'] ) AND is_numeric( $_POST['civicrm_directory_zoom'] ) ) {
			$zoom = absint( trim( $_POST['civicrm_directory_zoom'] ) );
		}
		$this->setting_set( 'zoom', $zoom );

		// save settings
		$this->settings_save();

		// construct Mapping Settings page URL
		$urls = $this->page_get_urls();
		$redirect = add_query_arg( 'updated', 'true', $urls['mapping'] );

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

		// CiviCRM group IDs that are directories
		$settings['group_ids'] = array( 0 => 0 );

		// default Google Maps key (empty)
		$settings['google_maps_key'] = '';

		// default map zoom level
		$settings['zoom'] = 14;

		// default map view location
		$settings['latitude'] = 0;
		$settings['longitude'] = 90;

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



} // class ends



