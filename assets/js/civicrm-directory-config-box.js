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
 * @since 0.1.2
 */
var CiviCRM_Directory_Config_Box = CiviCRM_Directory_Config_Box || {};



/**
 * Pass the jQuery shortcut in.
 *
 * @since 0.1.2
 *
 * @param {Object} $ The jQuery object.
 */
( function( $ ) {

	/**
	 * Create Settings Object.
	 *
	 * @since 0.1.2
	 */
	CiviCRM_Directory_Config_Box.settings = new function() {

		// prevent reference collisions
		var me = this;

		/**
		 * Initialise Settings.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.1.2
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
		 * @since 0.1.2
		 */
		this.init_localisation = function() {
			if ( 'undefined' !== typeof CiviCRM_Directory_Config_Box_Settings ) {
				me.localisation = CiviCRM_Directory_Config_Box_Settings.localisation;
			}
		};

		/**
		 * Getter for localisation.
		 *
		 * @since 0.1.2
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
		 * @since 0.1.2
		 */
		this.init_settings = function() {
			if ( 'undefined' !== typeof CiviCRM_Directory_Config_Box_Settings ) {
				me.settings = CiviCRM_Directory_Config_Box_Settings.settings;
			}
		};

		/**
		 * Getter for retrieving a setting.
		 *
		 * @since 0.1.2
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
	 * @since 0.1.2
	 */
	CiviCRM_Directory_Config_Box.box = new function() {

		// prevent reference collisions
		var me = this;

		/**
		 * Initialise.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.1.2
		 */
		this.init = function() {

		};

		/**
		 * Do setup when jQuery reports that the DOM is ready.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.1.2
		 */
		this.dom_ready = function() {

			/**
			 * Check the status of each of the checkboxes.
			 *
			 * @since 0.1.2
			 */
			$('.civicrm-directory-types').each( function() {

				var current_on,
					current_class,
					last_class,
					type_class;

				// get checked
				current_on = $(this).prop( 'checked' );

				// get class
				current_class = $(this).prop( 'class' );

				// get type ID
				last_class = current_class.split(' ')[1];
				type_class = last_class.split('-')[1];

				// show field if checked
				if ( current_on ) {
					$('.civicrm-directory-' + type_class).show();
				} else {
					$('.civicrm-directory-' + type_class).hide();
				}

			});

			var boxes = '.civicrm-directory-fields-other, ' +
				'.civicrm-directory-fields-address, ' +
				'.civicrm-directory-fields-phone';

			/**
			 * Check the status of each of the "other fields" checkboxes.
			 *
			 * @since 0.1.2
			 */
			$( boxes ).each( function() {

				var current_on, sub_div;

				// get checked
				current_on = $(this).prop( 'checked' );

				// get sub div
				sub_div = $(this).parent().next( 'div' );

				// show sub div if checked
				if ( current_on ) {
					sub_div.show();
				} else {
					sub_div.hide();
				}

			});

			// enable listeners
			me.listeners();

		};

		/**
		 * Initialise listeners.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.1.2
		 */
		this.listeners = function() {

			/**
			 * Listen for clicks on the checkboxes.
			 *
			 * @since 0.1.2
			 *
			 * @param {Object} e The click event object
			 */
			$('.civicrm-directory-types').on( 'click', function( event ) {

				var current_on,
					current_class,
					last_class,
					type_class;

				// get checked
				current_on = $(this).prop( 'checked' );

				// get class
				current_class = $(this).prop( 'class' );

				// get type ID
				last_class = current_class.split(' ')[1];
				type_class = last_class.split('-')[1];

				// show field if checked
				if ( current_on ) {
					$('.civicrm-directory-' + type_class).slideDown();
				} else {
					$('.civicrm-directory-' + type_class).slideUp();
				}

			});

			var boxes = '.civicrm-directory-fields-other, ' +
				'.civicrm-directory-fields-address, ' +
				'.civicrm-directory-fields-phone';

			/**
			 * Listen for clicks on the "other fields" checkboxes.
			 *
			 * @since 0.2
			 *
			 * @param {Object} e The click event object
			 */
			$( boxes ).on( 'click', function( event ) {

				var current_on, sub_div;

				// get checked
				current_on = $(this).prop( 'checked' );

				// get sub div
				sub_div = $(this).parent().next( 'div' );

				// show sub div if checked
				if ( current_on ) {
					sub_div.slideDown();
				} else {
					sub_div.slideUp();
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
 * @since 0.1.2
 */
jQuery(document).ready(function($) {

	// The DOM is loaded now
	CiviCRM_Directory_Config_Box.box.dom_ready();

}); // end document.ready()

