/**
 * CiviCRM Directory Map Javascript.
 *
 * Implements mapping functionality.
 *
 * @package Civi_Directory
 */



/**
 * Create CiviCRM Directory object.
 *
 * This works as a "namespace" of sorts, allowing us to hang properties, methods
 * and "sub-namespaces" from it.
 *
 * @since 0.1
 */
var CiviCRM_Directory_Map = CiviCRM_Directory_Map || {};



/**
 * Pass the jQuery shortcut in.
 *
 * @since 0.1
 *
 * @param {Object} $ The jQuery object.
 */
( function( $ ) {

	/**
	 * Create Settings Object.
	 *
	 * @since 0.1
	 */
	CiviCRM_Directory_Map.settings = new function() {

		// prevent reference collisions
		var me = this;

		/**
		 * Initialise Settings.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.1
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
		 * @since 0.1
		 */
		this.init_localisation = function() {
			if ( 'undefined' !== typeof CiviCRM_Directory_Map_Settings ) {
				me.localisation = CiviCRM_Directory_Map_Settings.localisation;
			}
		};

		/**
		 * Getter for localisation.
		 *
		 * @since 0.1
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
		 * @since 0.1
		 */
		this.init_settings = function() {
			if ( 'undefined' !== typeof CiviCRM_Directory_Map_Settings ) {
				me.settings = CiviCRM_Directory_Map_Settings.settings;
			}
		};

		/**
		 * Getter for retrieving a setting.
		 *
		 * @since 0.1
		 *
		 * @param {String} The identifier for the desired setting
		 * @return The value of the setting
		 */
		this.get_setting = function( identifier ) {
			return me.settings[identifier];
		};

	};

	// init settings
	CiviCRM_Directory_Map.settings.init();

	/**
	 * Create Mapping Object.
	 *
	 * @since 0.1
	 */
	CiviCRM_Directory_Map.map = new function() {

		// prevent reference collisions
		var me = this;

		// init markers array
		me.markers = [];

		/**
		 * Initialise.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.1
		 */
		this.init = function() {

			var locations = CiviCRM_Directory_Map.settings.get_setting( 'locations' );

			// define marker image
			me.pin = new google.maps.MarkerImage(
				CiviCRM_Directory_Map.settings.get_setting( 'pin_image_url' ),
				null, null, null, new google.maps.Size( 20, 20 )
			);

			// init map
			me.map = new google.maps.Map( document.getElementById( 'map-canvas' ), {
				zoom: CiviCRM_Directory_Map.settings.get_setting( 'zoom' ),
				center: new google.maps.LatLng(
					CiviCRM_Directory_Map.settings.get_setting( 'latitude' ),
					CiviCRM_Directory_Map.settings.get_setting( 'longitude' )
				)
			});

			// init info window
			me.info_window = new google.maps.InfoWindow();

			// handle clicks on map to close info winow
			google.maps.event.addListener( me.map, 'click', function() {
				me.info_window.close();
			});

			// create markers
			me.markers_create( locations );

		};

		/**
		 * Create markers.
		 *
		 * @since 0.1
		 */
		this.markers_create = function( locations ) {

			var marker;

			// loop through the location data
			for (var i = 0, location; location = locations[i++];) {

				// create marker
				marker = me.marker_create( location );

				// add to array of markers
				me.markers.push( marker );

			}

		};

		/**
		 * Clear markers.
		 *
		 * @since 0.1
		 */
		this.markers_clear = function() {

			// loop through the markers and remove from map
			for (var i = 0; i < me.markers.length; i++) {
				me.markers[i].setMap( null );
			}

		};

		/**
		 * Create marker for a location.
		 *
		 * @since 0.1
		 *
		 * @param {Object} location The data for the location
		 */
		this.marker_create = function( location ) {

			var marker, marker_position, window_content;

			// create marker position
			marker_position = new google.maps.LatLng(
				location.latitude,
				location.longitude
			);

			// create marker
			marker = new google.maps.Marker({
				position: marker_position,
				map: me.map,
				icon: me.pin
			});

			// create info window
			window_content = me.info_window_create( location.name, location.address, location.permalink );

			// listen for clicks on markers
			google.maps.event.addListener( marker, 'click',
				me.marker_clicked( marker, window_content )
			);

			// --<
			return marker;

		};

		/**
		 * Handle clicks on markers.
		 *
		 * @since 0.1
		 *
		 * @param {Object} marker The marker object
		 * @param {String} content The marker content
		 * @return {Object} Google Maps instruction set
		 */
		this.marker_clicked = function( marker, content ) {

			// --<
			return function() {
				me.info_window.setContent( content );
				me.info_window.open( me.map, marker );
			};

		};

		/**
		 * Create an info window.
		 *
		 * @since 0.1
		 *
		 * @param {String} title The info window title
		 * @param {String} content The info window content
		 * @param {String} url The info window target URL
		 * @return {String} info_window The info window markup
		 */
		this.info_window_create = function( title, content, url ) {

			var info_window, link_title;

			// open info window markup
			info_window = '<div class="map_infowindow">';

			// add title and content
			info_window += '<h3>' + title + '</h3>';
			info_window += '<p>' + content + '</p>';

			// optionally add link
			if ( url != '' ) {

				// retrieve localised window title
				link_title = CiviCRM_Directory_Map.settings.get_localisation( 'info_window_link_title' );

				// add to window markup
				info_window += '<p class="link"><a href="' + url + '">' + link_title + '</a></p>';

			}

			// close info window markup
			info_window += '</div>';

			// --<
			return info_window;

		};

	};

	// initialise the map
	google.maps.event.addDomListener( window, 'load', CiviCRM_Directory_Map.map.init() );

} )( jQuery );



