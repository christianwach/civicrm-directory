/**
 * CiviCRM Directory Browse Javascript.
 *
 * Implements browsing functionality.
 *
 * @package Civi_Directory
 */



/**
 * Create CiviCRM Directory Browse object.
 *
 * This works as a "namespace" of sorts, allowing us to hang properties, methods
 * and "sub-namespaces" from it.
 *
 * @since 0.1.1
 */
var CiviCRM_Directory_Browse = CiviCRM_Directory_Browse || {};



/**
 * Pass the jQuery shortcut in.
 *
 * @since 0.1.1
 *
 * @param {Object} $ The jQuery object.
 */
( function( $ ) {

	/**
	 * Create Settings Object.
	 *
	 * @since 0.1.1
	 */
	CiviCRM_Directory_Browse.settings = new function() {

		// prevent reference collisions
		var me = this;

		/**
		 * Initialise Settings.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.1.1
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
		 * @since 0.1.1
		 */
		this.init_localisation = function() {
			if ( 'undefined' !== typeof CiviCRM_Directory_Browse_Settings ) {
				me.localisation = CiviCRM_Directory_Browse_Settings.localisation;
			}
		};

		/**
		 * Getter for localisation.
		 *
		 * @since 0.1.1
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
		 * @since 0.1.1
		 */
		this.init_settings = function() {
			if ( 'undefined' !== typeof CiviCRM_Directory_Browse_Settings ) {
				me.settings = CiviCRM_Directory_Browse_Settings.settings;
			}
		};

		/**
		 * Getter for retrieving a setting.
		 *
		 * @since 0.1.1
		 *
		 * @param {String} The identifier for the desired setting
		 * @return The value of the setting
		 */
		this.get_setting = function( identifier ) {
			return me.settings[identifier];
		};

	};

	/**
	 * Create Browsing Object.
	 *
	 * @since 0.1.1
	 */
	CiviCRM_Directory_Browse.browse = new function() {

		// prevent reference collisions
		var me = this;

		// init "submission in progress" flag
		me.submitting = false;

		/**
		 * Initialise.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.1.1
		 */
		this.init = function() {

		};

		/**
		 * Do setup when jQuery reports that the DOM is ready.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.1.1
		 */
		this.dom_ready = function() {

			// enable listeners
			me.listeners();

		};

		/**
		 * Initialise listeners.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.1.1
		 */
		this.listeners = function() {

			// declare vars
			var links = $('.first-letter-link');

			/**
			 * Add a click event listener to letter links.
			 *
			 * @param {Object} event The event object
			 */
			links.on( 'click', function( event ) {

				// declare vars
				var letter;

				// prevent form submission
				if ( event.preventDefault ) {
					event.preventDefault();
				}

				// bail if a submission is in progress
				if( me.submitting === true ) return;

				// flag that a submission is in progress
				me.submitting = true;

				// grab letter
				letter = $(this).html();

				// send
				me.send( letter );

			});

		};

		/**
		 * Callback from AJAX request.
		 *
		 * @since 0.1.1
		 *
		 * @param {Array} data The data received from the server
		 */
		this.update = function( data ) {

			// replace listings markup
			$('.civicrm-directory .listing').html( data.listing );

			// broadcast
			$(document).trigger( 'civicrm-letter-loaded', [ data ] );

			// flag that a submission is finished
			me.submitting = false;

		};

		/**
		 * Send AJAX request.
		 *
		 * @since 0.1.1
		 *
		 * @param {Integer} letter The first letter to filter by.
		 */
		this.send = function( letter ) {

			// use jQuery post
			$.post(

				// URL to post to
				CiviCRM_Directory_Browse.settings.get_setting( 'ajax_url' ),

				{

					// token received by WordPress
					action: 'civicrm_directory_first_letter',

					// data to send
					first_letter: letter,
					post_id: CiviCRM_Directory_Browse.settings.get_setting( 'post_id' )

				},

				// callback
				function( data, textStatus ) {

					// if success
					if ( textStatus == 'success' ) {

						// update
						me.update( data );

					} else {

						// log error
						if ( console.log ) {
							console.log( textStatus );
						}

					}

				},

				// expected format
				'json'

			);

		};

	};

	// do immediate init
	CiviCRM_Directory_Browse.settings.init();
	CiviCRM_Directory_Browse.browse.init();

} )( jQuery );



/**
 * Trigger dom_ready methods where necessary.
 *
 * @since 0.1.1
 */
jQuery(document).ready(function($) {

	// The DOM is loaded now
	CiviCRM_Directory_Browse.browse.dom_ready();

}); // end document.ready()

