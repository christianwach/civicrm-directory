/**
 * CiviCRM Directory Search Javascript.
 *
 * Implements browsing functionality.
 *
 * @package Civi_Directory
 */



/**
 * Create CiviCRM Directory Search object.
 *
 * This works as a "namespace" of sorts, allowing us to hang properties, methods
 * and "sub-namespaces" from it.
 *
 * @since 0.1.1
 */
var CiviCRM_Directory_Search = CiviCRM_Directory_Search || {};



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
	CiviCRM_Directory_Search.settings = new function() {

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
			if ( 'undefined' !== typeof CiviCRM_Directory_Search_Settings ) {
				me.localisation = CiviCRM_Directory_Search_Settings.localisation;
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
			if ( 'undefined' !== typeof CiviCRM_Directory_Search_Settings ) {
				me.settings = CiviCRM_Directory_Search_Settings.settings;
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
	 * Create Search Object.
	 *
	 * @since 0.1.1
	 */
	CiviCRM_Directory_Search.search = new function() {

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

			// set up form
			me.setup();

			// enable listeners
			me.listeners();

		};

		/**
		 * Set up form.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.2.4
		 */
		this.setup = function() {

			// grab form reference
			me.form = $('#civicrm_directory_search');

			// init AJAX spinner
			me.form.append(
				'<img src="' + CiviCRM_Directory_Search.settings.get_setting( 'ajax_loader' ) + '" id="civicrm_directory_search_loading" />'
			);

			// hide spinner
			$('#civicrm_directory_search_loading').hide();

		};

		/**
		 * Initialise listeners.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.1.1
		 */
		this.listeners = function() {

			/**
			 * Intercept search submissions.
			 *
			 * @since 0.1.1
			 */
			me.form.on( 'submit', function( event ) {

				var search;

				// prevent form submission
				if ( event.preventDefault ) {
					event.preventDefault();
				}

				// bail if a submission is in progress
				if( me.submitting === true ) return;

				// flag that a submission is in progress
				me.submitting = true;

				// show spinner
				$('#civicrm_directory_search_loading').show();

				// find search string
				search = me.form.find( '#civicrm_directory_search_string' ).val();

				// send AJAX request
				me.send( search );

				// --<
				return false;

			}); // end form.submit()

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
			$(document).trigger( 'civicrm-search-loaded', [ data ] );

			// hide spinner
			$('#civicrm_directory_search_loading').hide();

			// flag that a submission is finished
			me.submitting = false;

		};

		/**
		 * Send AJAX request.
		 *
		 * @since 0.1.1
		 *
		 * @param {Integer} search The search string to filter by.
		 */
		this.send = function( search ) {

			// use jQuery post
			$.post(

				// URL to post to
				CiviCRM_Directory_Search.settings.get_setting( 'ajax_url' ),

				{

					// token received by WordPress
					action: 'civicrm_directory_search',

					// data to send
					search: search,
					post_id: CiviCRM_Directory_Search.settings.get_setting( 'post_id' )

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
	CiviCRM_Directory_Search.settings.init();
	CiviCRM_Directory_Search.search.init();

} )( jQuery );



/**
 * Trigger dom_ready methods where necessary.
 *
 * @since 0.1.1
 */
jQuery(document).ready(function($) {

	// The DOM is loaded now
	CiviCRM_Directory_Search.search.dom_ready();

}); // end document.ready()

