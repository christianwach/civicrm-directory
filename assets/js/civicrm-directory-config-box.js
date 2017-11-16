/**
 * CiviCRM Directory Config Box Javascript.
 *
 * Implements functionality on the Config metabox.
 *
 * @package Civi_Directory
 */



/**
 * Create CiviCRM Directory Config Box object.
 *
 * This works as a "namespace" of sorts, allowing us to hang properties, methods
 * and "sub-namespaces" from it.
 *
 * @since 0.2.7
 */
var CiviCRM_Directory_Config_Box = CiviCRM_Directory_Config_Box || {};



/**
 * Pass the jQuery shortcut in.
 *
 * @since 0.2.7
 *
 * @param {Object} $ The jQuery object.
 */
( function( $ ) {

	/**
	 * Create Settings Object.
	 *
	 * @since 0.2.7
	 */
	CiviCRM_Directory_Config_Box.settings = new function() {

		// prevent reference collisions
		var me = this;

		/**
		 * Initialise Settings.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.2.7
		 */
		this.init = function() {

			// init localisation
			me.init_localisation();

			// init settings
			me.init_settings();

		};

		// init localisation array
		me.localisation = [];

		/**
		 * Init localisation from settings object.
		 *
		 * @since 0.2.7
		 */
		this.init_localisation = function() {
			if ( 'undefined' !== typeof CiviCRM_Directory_Config_Box_Settings ) {
				me.localisation = CiviCRM_Directory_Config_Box_Settings.localisation;
			}
		};

		/**
		 * Getter for localisation.
		 *
		 * @since 0.2.7
		 *
		 * @param {String} The identifier for the desired localisation string
		 * @return {String} The localised string
		 */
		this.get_localisation = function( identifier ) {
			return me.localisation[identifier];
		};

		// init settings array
		me.settings = [];

		/**
		 * Init settings from settings object.
		 *
		 * @since 0.2.7
		 */
		this.init_settings = function() {
			if ( 'undefined' !== typeof CiviCRM_Directory_Config_Box_Settings ) {
				me.settings = CiviCRM_Directory_Config_Box_Settings.settings;
			}
		};

		/**
		 * Getter for retrieving a setting.
		 *
		 * @since 0.2.7
		 *
		 * @param {String} The identifier for the desired setting
		 * @return The value of the setting
		 */
		this.get_setting = function( identifier ) {
			return me.settings[identifier];
		};

	};

	/**
	 * Create Config_Box Object.
	 *
	 * @since 0.2.7
	 */
	CiviCRM_Directory_Config_Box.box = new function() {

		// prevent reference collisions
		var me = this;

		/**
		 * Initialise.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.2.7
		 */
		this.init = function() {

		};

		/**
		 * Do setup when jQuery reports that the DOM is ready.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.2.7
		 */
		this.dom_ready = function() {

			var map_checkbox = '#' + CiviCRM_Directory_Config_Box.settings.get_setting( 'map_enabled' ),
				height_el = '.' + CiviCRM_Directory_Config_Box.settings.get_setting( 'map_height' ),
				maps_on;

			// get checked
			maps_on = $(map_checkbox).prop( 'checked' );

			// show field if checked
			if ( maps_on ) {
				$(height_el).show();
			} else {
				$(height_el).hide();
			}

			// enable listeners
			me.listeners();

		};

		/**
		 * Initialise listeners.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.2.7
		 */
		this.listeners = function() {

			var map_checkbox = '#' + CiviCRM_Directory_Config_Box.settings.get_setting( 'map_enabled' ),
				height_el = '.' + CiviCRM_Directory_Config_Box.settings.get_setting( 'map_height' );

			/**
			 * Listen for clicks on the "Maps Enabled" checkbox.
			 *
			 * @since 0.2
			 *
			 * @param {Object} e The click event object
			 */
			$(map_checkbox).on( 'click', function( event ) {

				var maps_on;

				// get checked
				maps_on = $(this).prop( 'checked' );

				// show field if checked
				if ( maps_on ) {
					$(height_el).show();
				} else {
					$(height_el).hide();
				}

			});

		};

	};

	// do immediate init
	CiviCRM_Directory_Config_Box.settings.init();
	CiviCRM_Directory_Config_Box.box.init();

} )( jQuery );



/**
 * Trigger dom_ready methods where necessary.
 *
 * @since 0.2.7
 */
jQuery(document).ready(function($) {

	// The DOM is loaded now
	CiviCRM_Directory_Config_Box.box.dom_ready();

}); // end document.ready()

