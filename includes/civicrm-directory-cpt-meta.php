<?php
/**
 * CPT Meta Class.
 *
 * Handles all metadata functionality for the Directory CPT.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CPT Meta Class.
 *
 * A class that encapsulates all metadata functionality for the Directory CPT.
 *
 * @since 0.1
 */
class CiviCRM_Directory_CPT_Meta {

	/**
	 * Custom Post Type name.
	 *
	 * @since 0.1
	 * @access public
	 * @var str $post_type_name The name of the Custom Post Type.
	 */
	public $post_type_name = 'directory';

	/**
	 * CiviCRM Contact Types meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var str $contact_types_meta_key The meta key for Contact Types.
	 */
	public $contact_types_meta_key = 'civicrm_directory_contact_types';

	/**
	 * CiviCRM Contact Fields meta key.
	 *
	 * @since 0.1.2
	 * @access public
	 * @var str $contact_types_meta_key The meta key for Contact Fields.
	 */
	public $contact_fields_meta_key = 'civicrm_directory_contact_fields';

	/**
	 * CiviCRM Group ID meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var str $group_id_meta_key The meta key for the Group ID.
	 */
	public $group_id_meta_key = 'civicrm_directory_group_id';

	/**
	 * Mapping Enabled meta key.
	 *
	 * @since 0.2.4
	 * @access public
	 * @var str $mapping_meta_key The meta key for the Mapping Enabled setting.
	 */
	public $mapping_meta_key = 'civicrm_directory_mapping';

	/**
	 * Map Height meta key.
	 *
	 * @since 0.2.7
	 * @access public
	 * @var str $mapping_height_meta_key The meta key for the Map Height setting.
	 */
	public $mapping_height_meta_key = 'civicrm_directory_map_height';

	/**
	 * "Browse by First Letter" meta key.
	 *
	 * @since 0.2.6
	 * @access public
	 * @var str $letter_meta_key The meta key for the "Browse by First Letter" setting.
	 */
	public $letter_meta_key = 'civicrm_directory_letter';

	/**
	 * "Search Form" meta key.
	 *
	 * @since 0.2.6
	 * @access public
	 * @var str $letter_meta_key The meta key for the "Search Form" setting.
	 */
	public $search_meta_key = 'civicrm_directory_search';

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

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// Add meta boxes.
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		// Intercept save.
		add_action( 'save_post', [ $this, 'save_post' ], 1, 2 );

	}

	// #########################################################################

	/**
	 * Adds metaboxes to admin screens.
	 *
	 * @since 0.1
	 */
	public function add_meta_boxes() {

		// Add our Group ID meta box.
		add_meta_box(
			'civicrm_directory_group_id',
			__( 'CiviCRM Group', 'civicrm-directory' ),
			[ $this, 'group_id_metabox' ],
			$this->post_type_name,
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

		// Add our Configuration meta box.
		add_meta_box(
			'civicrm_directory_config',
			__( 'Directory Configuration', 'civicrm-directory' ),
			[ $this, 'config_metabox' ],
			$this->post_type_name,
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

		// Add our Contacts Configuration meta box.
		add_meta_box(
			'civicrm_directory_contact_types',
			__( 'Directory Contacts Configuration', 'civicrm-directory' ),
			[ $this, 'contacts_metabox' ],
			$this->post_type_name,
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

	}

	// #########################################################################

	/**
	 * Adds a metabox to CPT edit screens for CiviCRM Group ID.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
	public function group_id_metabox( $post ) {

		// Use nonce for verification.
		wp_nonce_field( 'civicrm_directory_group_id_box', 'civicrm_directory_group_id_nonce' );

		// Get group ID from post meta.
		$group_id = $this->group_id_get( $post->ID );

		// Instructions.
		echo '<p>' . __( 'Choose the CiviCRM Group to which all Contacts for this Directory belong.', 'civicrm-directory' ) . '</p>';

		// Start with empty option.
		$selected = empty( $group_id ) ? ' selected="selected"' : '';
		$options = '<option value=""' . $selected . '>' . __( '- Select a Group -', 'civicrm-directory' ) . '</option>';

		// Get CiviCRM groups that could be Directories.
		$groups = $this->plugin->civi->groups_get();

		// Add CiviCRM groups.
		foreach ( $groups as $key => $data ) {
			$selected = ( $key == $group_id ) ? ' selected="selected"' : '';
			$options .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $data['title'] ) . '</option>';
		}

		// Show the dropdown.
		echo '<p><select name="' . $this->group_id_meta_key . '" id="' . $this->group_id_meta_key . '">' .
			$options .
		'</select></p>';

	}

	/**
	 * Get the CiviCRM Group ID for a Directory ID.
	 *
	 * @since 0.2.4
	 *
	 * @param int $post_id The ID of the directory.
	 * @return int|bool $group_id The ID of the CiviCRM Group, or false on failure.
	 */
	public function group_id_get( $post_id = null ) {

		// Use current post if none passed.
		if ( is_null( $post_id ) ) {
			$post_id = get_the_ID();
		}

		// Set key.
		$db_key = '_' . $this->group_id_meta_key;

		// Default to false.
		$group_id = false;

		// Get value if the custom field already has one.
		$existing = get_post_meta( $post_id, $db_key, true );
		if ( false !== $existing ) {
			$group_id = get_post_meta( $post_id, $db_key, true );
		}

		// --<
		return $group_id;

	}

	// #########################################################################

	/**
	 * Adds a metabox to CPT edit screens for Configuration preferences.
	 *
	 * @since 0.2.4
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
	public function config_metabox( $post ) {

		// Use nonce for verification.
		wp_nonce_field( 'civicrm_directory_config_box', 'civicrm_directory_config_nonce' );

		// Get mapping setting from post meta.
		$mapping = $this->mapping_get( $post->ID );

		// Mapping enabled?
		$checked = $mapping ? ' checked="checked"' : '';

		// Show checkbox.
		echo '<p>' .
			'<label>' .
				'<input type="checkbox" id="' . $this->mapping_meta_key . '" name="' . $this->mapping_meta_key . '" value="1"' . $checked . '> ' .
				__( 'This Directory shows a map.', 'civicrm-directory' ) .
			'</label>' .
		'</p>';

		// Show map config if enabled.
		if ( $mapping ) {

			// Get mapping height setting from post meta.
			$height = $this->mapping_height_get( $post->ID );

			// Show input.
			echo '<p class="' . $this->mapping_height_meta_key . '">' .
				'<label>' .
					__( 'The height of the map in pixels:', 'civicrm-directory' ) .
					' <input type="text" id="' . $this->mapping_height_meta_key . '" name="' . $this->mapping_height_meta_key . '" value="' . esc_attr( $height ) . '"/ > ' .
				'</label>' .
			'</p>';

		}

		// Get first letter setting from post meta.
		$letter = $this->letter_get( $post->ID );

		// Browse by first letter enabled?
		$checked = $letter ? ' checked="checked"' : '';

		// Show checkbox.
		echo '<p>' .
			'<label>' .
				'<input type="checkbox" name="' . $this->letter_meta_key . '" value="1"' . $checked . '> ' .
				__( 'This Directory shows a "Browse by First Letter" section.', 'civicrm-directory' ) .
			'</label>' .
		'</p>';

		// Get "Search Form" setting from post meta.
		$search = $this->search_get( $post->ID );

		// "Search Form" enabled?
		$checked = $search ? ' checked="checked"' : '';

		// Show checkbox.
		echo '<p>' .
			'<label>' .
				'<input type="checkbox" name="' . $this->search_meta_key . '" value="1"' . $checked . '> ' .
				__( 'This Directory shows a Search Form.', 'civicrm-directory' ) .
			'</label>' .
		'</p>';

		// ---------------------------------------------------------------------

		// Add our metabox javascript in the footer.
		wp_enqueue_script(
			'civicrm_directory_config_box_js',
			CIVICRM_DIRECTORY_URL . '/assets/js/civicrm-directory-config-box.js',
			[ 'jquery' ],
			CIVICRM_DIRECTORY_VERSION,
			true
		);

		// Init localisation.
		$localisation = [];

		// Init settings.
		$settings = [
			'map_enabled' => $this->mapping_meta_key,
			'map_height' => $this->mapping_height_meta_key,
		];

		// Localisation array.
		$vars = [
			'localisation' => $localisation,
			'settings' => $settings,
		];

		// Localise.
		wp_localize_script(
			'civicrm_directory_config_box_js',
			'CiviCRM_Directory_Config_Box_Settings',
			$vars
		);

	}

	/**
	 * Get the Mapping Enabled setting for a Directory ID.
	 *
	 * @since 0.2.4
	 *
	 * @param int $post_id The ID of the directory.
	 * @return bool $mapping True if mapping is enabled for the Directory, false otherwise.
	 */
	public function mapping_get( $post_id = null ) {

		// Use current post if none passed.
		if ( is_null( $post_id ) ) {
			$post_id = get_the_ID();
		}

		// Set key.
		$db_key = '_' . $this->mapping_meta_key;

		// Default to false.
		$mapping = false;

		// Get value if the custom field already has one.
		$existing = get_post_meta( $post_id, $db_key, true );
		if ( false !== $existing ) {
			$mapping = get_post_meta( $post_id, $db_key, true );
		}

		// Anything but '1' is mapping off.
		if ( ! empty( $mapping ) ) {
			$mapping = true;
		}

		// --<
		return $mapping;

	}

	/**
	 * Get the Map Height setting for a Directory ID.
	 *
	 * @since 0.2.7
	 *
	 * @param int $post_id The ID of the directory.
	 * @return int|bool $height The hieght of the map if set, false otherwise.
	 */
	public function mapping_height_get( $post_id = null ) {

		// Use current post if none passed.
		if ( is_null( $post_id ) ) {
			$post_id = get_the_ID();
		}

		// Set key.
		$db_key = '_' . $this->mapping_height_meta_key;

		// Default to false.
		$height = false;

		// Get value if the custom field already has one.
		$existing = get_post_meta( $post_id, $db_key, true );
		if ( false !== $existing ) {
			$height = get_post_meta( $post_id, $db_key, true );
		}

		// Handle empty values by substituting default.
		if ( empty( $height ) ) {
			$height = $this->plugin->admin->setting_get( 'google_maps_height' );
		}

		// Cast as numeric in all cases.
		$height = absint( $height );

		/**
		 * Allow default map height to be filtered.
		 *
		 * @since 0.2.7
		 *
		 * @param int $height The height of the map in pixels.
		 */
		return apply_filters( 'civicrm_directory_map_height', $height );

	}

	/**
	 * Get the "Browse by First Letter" Enabled setting for a Directory ID.
	 *
	 * @since 0.2.6
	 *
	 * @param int $post_id The ID of the directory.
	 * @return bool $letter True if "Browse by First Letter" is enabled for the Directory, false otherwise.
	 */
	public function letter_get( $post_id = null ) {

		// Use current post if none passed.
		if ( is_null( $post_id ) ) {
			$post_id = get_the_ID();
		}

		// Set key.
		$db_key = '_' . $this->letter_meta_key;

		// Default to false.
		$letter = false;

		// Get value if the custom field already has one.
		$existing = get_post_meta( $post_id, $db_key, true );
		if ( false !== $existing ) {
			$letter = get_post_meta( $post_id, $db_key, true );
		}

		// Anything but '1' is "Browse by First Letter" off.
		if ( ! empty( $letter ) ) {
			$letter = true;
		}

		// --<
		return $letter;

	}

	/**
	 * Get the "Search Form" Enabled setting for a Directory ID.
	 *
	 * @since 0.2.6
	 *
	 * @param int $post_id The ID of the directory.
	 * @return bool $letter True if "Search Form" is enabled for the Directory, false otherwise.
	 */
	public function search_get( $post_id = null ) {

		// Use current post if none passed.
		if ( is_null( $post_id ) ) {
			$post_id = get_the_ID();
		}

		// Set key.
		$db_key = '_' . $this->search_meta_key;

		// Default to false.
		$search = false;

		// Get value if the custom field already has one.
		$existing = get_post_meta( $post_id, $db_key, true );
		if ( false !== $existing ) {
			$search = get_post_meta( $post_id, $db_key, true );
		}

		// Anything but '1' is "Search Form" off.
		if ( ! empty( $search ) ) {
			$search = true;
		}

		// --<
		return $search;

	}

	// #########################################################################

	/**
	 * Adds a metabox to CPT edit screens for Contacts Configuration.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
	public function contacts_metabox( $post ) {

		// Sanity check.
		if ( ! ( $post instanceof WP_Post ) ) {
			return;
		}

		// Use nonce for verification.
		wp_nonce_field( 'civicrm_directory_contacts_box', 'civicrm_directory_contacts_nonce' );

		// ---------------------------------------------------------------------

		// Get contact types for this post.
		$contact_types = $this->contact_types_get( $post->ID );

		// Get all contact types.
		$all_contact_types = $this->plugin->civi->contact_types_get_all();

		// Instructions.
		echo '<p>' . __( 'Choose the kinds of CiviCRM Contact Types for the Contacts in this Directory. This is useful because if, for example, you know that all of the Contacts in this Directory will be Organisations then it makes searching the Directory more efficient. You can change this setting if you need to.', 'civicrm-directory' ) . '</p>';

		// Open a list.
		echo '<ul>';

		// Show checkboxes for each contact type.
		foreach ( $all_contact_types as $contact_type ) {

			// Is it checked?
			$checked = '';
			if ( in_array( $contact_type['name'], $contact_types ) ) {
				$checked = ' checked="checked"';
			}

			// Show checkbox.
			echo '<li>' .
				'<label>' .
					'<input type="checkbox" name="' . $this->contact_types_meta_key . '[]" value="' . esc_attr( $contact_type['name'] ) . '" class="civicrm-directory-types ' . $this->contact_types_meta_key . '-' . esc_attr( $contact_type['name'] ) . '"' . $checked . '> ' .
					'<strong>' . esc_html( $contact_type['name'] ) . '</strong>' .
				'</label>' .
			'</li>';

		}

		// Close list.
		echo '</ul>';

		// ---------------------------------------------------------------------

		// Get contact fields data.
		$contact_fields = $this->contact_fields_get( $post->ID );

		// Let's have some style.
		echo '
			<style type="text/css">
				.civicrm-directory-fields {
					display: none;
					border: 1px solid #ddd;
					padding: 0 1em;
					margin-bottom: 1em;
					background-color: #fcfcfc;
				}

				.civicrm-directory-fields > h3 {
					cursor: pointer;
					text-transform: uppercase;
					margin: 0;
					padding: 0.5em 0;
				}

				.civicrm-directory-fields-container {
					display: none;
				}

				.civicrm-directory-fields .sep-core {
					margin-top: 0;
				}

				.civicrm-directory-fields h4 {
					margin: 1em 0 0 0;
					text-transform: uppercase;
				}

				.civicrm-directory-fields h4:first-child {
					margin: 0;
				}

				.civicrm-directory-fields ul {
					margin: 0.5em 0 1em 0;
				}

				.civicrm-directory-fields div.sub {
					background-color: #fff;
				}

				.civicrm-directory-fields div.sub-sub {
					background-color: #fcfcfc;
				}

				.civicrm-directory-fields div.sub,
				.civicrm-directory-fields div.sub-sub {
					display: none;
					margin: 1em 0 1em 0em;
					border: 1px solid #ddd;
					padding: 0.5em 1em 0.2em;
				}
			</style>';

		// Open div.
		echo '<div class="civicrm-directory-fields civicrm-directory-Individual">';

		// Show header.
		echo '<h3>' . __( 'Fields for Individuals', 'civicrm-directory' ) . '</h3>';

		// Open fields container.
		echo '<div class="civicrm-directory-fields-container">';

		// Print Individual fields.
		$this->fields_core_render( $post, $contact_fields, 'Individual' );

		// Print Individual custom fields.
		$this->fields_custom_render( $post, $contact_fields, 'Individual' );

		// Print Individual other fields.
		$this->fields_other_render( $post, $contact_fields, 'Individual' );

		/**
		 * Allow further fields to be injected.
		 *
		 * @since 0.2
		 *
		 * @param WP_Post $post The object for the current post/page.
		 * @param array $contact_fields The chosen fields stored in the post meta.
		 * @param str $type The contact type for which fields should be retrieved.
		 */
		do_action( 'contacts_metabox_fields', $post, $contact_fields, 'Individual' );

		// Close div.
		echo '</div>';

		// Close div.
		echo '</div>';

		// Open div.
		echo '<div class="civicrm-directory-fields civicrm-directory-Household">';

		// Show header.
		echo '<h3>' . __( 'Fields for Households', 'civicrm-directory' ) . '</h3>';

		// Open fields container.
		echo '<div class="civicrm-directory-fields-container">';

		// Print Household fields.
		$this->fields_core_render( $post, $contact_fields, 'Household' );

		// Print Household custom fields.
		$this->fields_custom_render( $post, $contact_fields, 'Household' );

		// Print Household other fields.
		$this->fields_other_render( $post, $contact_fields, 'Household' );

		/**
		 * Allow further fields to be injected.
		 *
		 * @since 0.2
		 *
		 * @param WP_Post $post The object for the current post/page.
		 * @param array $contact_fields The chosen fields stored in the post meta.
		 * @param str $type The contact type for which fields should be retrieved.
		 */
		do_action( 'contacts_metabox_fields', $post, $contact_fields, 'Household' );

		// Close div.
		echo '</div>';

		// Close div.
		echo '</div>';

		// Open div.
		echo '<div class="civicrm-directory-fields civicrm-directory-Organization">';

		// Show header.
		echo '<h3>' . __( 'Fields for Organizations', 'civicrm-directory' ) . '</h3>';

		// Open fields container.
		echo '<div class="civicrm-directory-fields-container">';

		// Print Organization fields.
		$this->fields_core_render( $post, $contact_fields, 'Organization' );

		// Print Organization custom fields.
		$this->fields_custom_render( $post, $contact_fields, 'Organization' );

		// Print Organization other fields.
		$this->fields_other_render( $post, $contact_fields, 'Organization' );

		/**
		 * Allow further fields to be injected.
		 *
		 * @since 0.2
		 *
		 * @param WP_Post $post The object for the current post/page.
		 * @param array $contact_fields The chosen fields stored in the post meta.
		 * @param str $type The contact type for which fields should be retrieved.
		 */
		do_action( 'contacts_metabox_fields', $post, $contact_fields, 'Organization' );

		// Close div.
		echo '</div>';

		// Close div.
		echo '</div>';

		// ---------------------------------------------------------------------

		// Add our metabox javascript in the footer.
		wp_enqueue_script(
			'civicrm_directory_contacts_box_js',
			CIVICRM_DIRECTORY_URL . '/assets/js/civicrm-directory-contacts-box.js',
			[ 'jquery' ],
			CIVICRM_DIRECTORY_VERSION,
			true
		);

		// Init localisation.
		$localisation = [];

		// Init settings.
		$settings = [];

		// Localisation array.
		$vars = [
			'localisation' => $localisation,
			'settings' => $settings,
		];

		// Localise.
		wp_localize_script(
			'civicrm_directory_contacts_box_js',
			'CiviCRM_Directory_Contacts_Box_Settings',
			$vars
		);

	}

	/**
	 * Renders the field checkboxes for a specified type of Contact.
	 *
	 * @since 0.1.2
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @param array $contact_fields The chosen fields stored in the post meta.
	 * @param str $type The contact type for which fields should be retrieved.
	 * @return bool True if list rendered, false otherwise.
	 */
	public function fields_core_render( $post, $contact_fields = [], $type = 'Individual' ) {

		// Init types array.
		$types = [ 'Contact' ];

		// Add passed in type.
		$types[] = $type;

		// Get all public contact fields.
		$all_contact_fields = $this->plugin->civi->contact_fields_get( $types, 'public' );

		// Bail if we get none.
		if ( count( $all_contact_fields ) === 0 ) {
			return false;
		}

		// Separator.
		echo '<hr class="sep-core">';

		// Let's have a heading.
		echo '<h4>' . __( 'Core Fields', 'civicrm-directory' ) . '</h4>';

		// Open a list.
		echo '<ul>';

		// Show checkboxes for each contact type.
		foreach ( $all_contact_fields as $contact_field ) {

			// Is it checked?
			$checked = '';
			if (
				isset( $contact_fields[ $type ]['core'] ) &&
				in_array( $contact_field['name'], $contact_fields[ $type ]['core'] )
			) {
				$checked = ' checked="checked"';
			}

			// Show checkbox.
			echo '<li>' .
				'<label>' .
					'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][core][]" value="' . esc_attr( $contact_field['name'] ) . '"' . $checked . '> ' .
					'<strong>' . esc_html( $contact_field['title'] ) . '</strong>' .
				'</label>' .
			'</li>';

		}

		// Close list.
		echo '</ul>';

		// --<
		return true;

	}

	/**
	 * Renders the custom field checkboxes for a specified type of Contact.
	 *
	 * @since 0.1.2
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @param array $contact_fields The chosen fields stored in the post meta.
	 * @param str $type The contact type for which fields should be retrieved.
	 * @return bool True if list rendered, false otherwise.
	 */
	public function fields_custom_render( $post, $contact_fields = [], $type = 'Individual' ) {

		// Init types array.
		$types = [ 'Contact' ];

		// Add passed in type.
		$types[] = $type;

		// Get all contact custom fields.
		$all_contact_custom_fields = $this->plugin->civi->contact_custom_fields_get( $types );

		// Bail if we get none.
		if ( count( $all_contact_custom_fields ) === 0 ) {
			return false;
		}

		// Extract just the data we need.
		$custom_fields = [];
		foreach ( $all_contact_custom_fields as $key => $value ) {
			$custom_fields[ $value['id'] ] = $value['label'];
		}

		// Separator.
		echo '<hr>';

		// Let's have a heading.
		echo '<h4>' . __( 'Custom Fields', 'civicrm-directory' ) . '</h4>';

		// Open a list.
		echo '<ul>';

		// Show checkboxes for each contact type.
		foreach ( $custom_fields as $key => $title ) {

			// Is it checked?
			$checked = '';
			if (
				isset( $contact_fields[ $type ]['custom'] ) &&
				in_array( $key, $contact_fields[ $type ]['custom'] )
			) {
				$checked = ' checked="checked"';
			}

			// Show checkbox.
			echo '<li>' .
				'<label>' .
					'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][custom][]" value="' . esc_attr( $key ) . '"' . $checked . '> ' .
					'<strong>' . esc_html( $title ) . '</strong>' .
				'</label>' .
			'</li>';

		}

		// Close list.
		echo '</ul>';

		// --<
		return true;

	}

	/**
	 * Renders the checkboxes for other fields for a specified type of Contact.
	 *
	 * @since 0.2
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @param array $contact_fields The chosen fields stored in the post meta.
	 * @param str $type The contact type for which fields should be retrieved.
	 * @return bool True if list rendered, false otherwise.
	 */
	public function fields_other_render( $post, $contact_fields = [], $type = 'Individual' ) {

		// What do we want?
		$other_fields = [
			'address' => __( 'Address', 'civicrm-directory' ),
			'phone' => __( 'Phone', 'civicrm-directory' ),
			'website' => __( 'Website', 'civicrm-directory' ),
			'email' => __( 'Email', 'civicrm-directory' ),
		];

		// Separator.
		echo '<hr>';

		// Let's have a heading.
		echo '<h4>' . __( 'Other Fields', 'civicrm-directory' ) . '</h4>';

		// Open a list.
		echo '<ul>';

		// Show UI for each field type.
		foreach ( $other_fields as $key => $title ) {

			// Open list item.
			echo '<li>';

			// Is it checked?
			$checked = '';
			if (
				isset( $contact_fields[ $type ]['other'] ) &&
				in_array( $key, $contact_fields[ $type ]['other'] )
			) {
				$checked = ' checked="checked"';
			}

			// Show checkbox.
			echo '<label>' .
				'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][other][]" value="' . esc_attr( $key ) . '"' . $checked . ' class="civicrm-directory-fields-other"> ' .
				'<strong>' . esc_html( $title ) . '</strong>' .
			'</label>';

			// Open a block-level element.
			echo '<div class="sub">';

			// Switch by key.
			switch ( $key ) {

				case 'address':
					echo $this->field_address_get( $post, $contact_fields, $type );
					break;

				case 'phone':
					echo $this->field_phone_get( $post, $contact_fields, $type );
					break;

				case 'website':
					echo $this->field_website_get( $post, $contact_fields, $type );
					break;

				case 'email':
					echo $this->field_email_get( $post, $contact_fields, $type );
					break;

			}

			// Close block-level element.
			echo '</div>';

			// Close list item.
			echo '</li>';

		}

		// Close list.
		echo '</ul>';

		// --<
		return true;

	}

	/**
	 * Construct UI for the Address field.
	 *
	 * @since 0.2
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @param array $contact_fields The chosen fields stored in the post meta.
	 * @param str $type The contact type for which fields should be retrieved.
	 * @return str The constructed field.
	 */
	public function field_address_get( $post, $contact_fields = [], $type = 'Individual' ) {

		// Init return.
		$markup = '';

		// Get address types.
		$address_types = civicrm_api( 'Address', 'getoptions', [
			'version' => 3,
			'sequential' => 1,
			'field' => 'location_type_id',
		]);

		// The fields we want to render.
		$fields = [
			'street_address',
			'supplemental_address_1',
			'supplemental_address_2',
			'city',
			'state_province_id',
			'postal_code',
			'country_id',
		];

		// Get all address fields.
		$address_fields = civicrm_api( 'Address', 'getfields', [
			'version' => 3,
			'sequential' => 1,
		]);

		// Open a list.
		$markup .= '<ul>';

		// We need a checkbox for each type.
		foreach ( $address_types['values'] as $address_type ) {

			// Open a list item.
			$markup .= '<li>';

			// Is it checked?
			$checked = '';
			if (
				isset( $contact_fields[ $type ]['address']['enabled'] ) &&
				in_array( $address_type['key'], $contact_fields[ $type ]['address']['enabled'] )
			) {
				$checked = ' checked="checked"';
			}

			// Show checkbox.
			$markup .= '<label>' .
				'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][address][enabled][]" value="' . esc_attr( $address_type['key'] ) . '"' . $checked . ' class="civicrm-directory-fields-address"> ' .
				'<strong>' . esc_html( $address_type['value'] ) . '</strong>' .
			'</label>';

			// Open a block-level element.
			$markup .= '<div class="sub-sub">';

			// Open a list.
			$markup .= '<ul>';

			// Show checkboxes for fields.
			foreach ( $address_fields['values'] as $address_field ) {

				// Open a list item.
				$markup .= '<li>';

				// Skip if not a field we want to render.
				if ( ! in_array( $address_field['name'], $fields ) ) {
					continue;
				}

				// Is it checked?
				$checked = '';
				if (
					isset( $contact_fields[ $type ]['address'][ $address_type['key'] ] ) &&
					in_array( $address_field['name'], $contact_fields[ $type ]['address'][ $address_type['key'] ] )
				) {
					$checked = ' checked="checked"';
				}

				// Show checkbox.
				$markup .= '<label>' .
					'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][address][' . $address_type['key'] . '][]" value="' . esc_attr( $address_field['name'] ) . '"' . $checked . '> ' .
					'<strong>' . esc_html( $address_field['title'] ) . '</strong>' .
				'</label>';

				// Close list item.
				$markup .= '</li>';

			}

			// Close list.
			$markup .= '</ul>';

			// Close block-level element.
			$markup .= '</div>';

			// Close list item.
			$markup .= '</li>';

		}

		// Close list.
		$markup .= '</ul>';

		// --<
		return $markup;

	}

	/**
	 * Construct UI for the Phone field.
	 *
	 * @since 0.2
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @param array $contact_fields The chosen fields stored in the post meta.
	 * @param str $type The contact type for which fields should be retrieved.
	 * @return str The constructed field.
	 */
	public function field_phone_get( $post, $contact_fields = [], $type = 'Individual' ) {

		// Init return.
		$markup = '';

		// Get all phone types.
		$phone_types = civicrm_api( 'Phone', 'getoptions', [
			'version' => 3,
			'sequential' => 1,
			'field' => 'location_type_id',
		]);

		// Get all phone fields.
		$phone_fields = civicrm_api( 'Phone', 'getfields', [
			'version' => 3,
			'sequential' => 1,
		]);

		// The fields we want to render.
		$fields = [
			'phone',
			'phone_type_id',
		];

		// Open a list.
		$markup .= '<ul>';

		// We need a checkbox for each type.
		foreach ( $phone_types['values'] as $phone_type ) {

			// Open a list item.
			$markup .= '<li>';

			// Is it checked?
			$checked = '';
			if (
				isset( $contact_fields[ $type ]['phone']['enabled'] ) &&
				in_array( $phone_type['key'], $contact_fields[ $type ]['phone']['enabled'] )
			) {
				$checked = ' checked="checked"';
			}

			// Show checkbox.
			$markup .= '<label>' .
				'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][phone][enabled][]" value="' . esc_attr( $phone_type['key'] ) . '"' . $checked . ' class="civicrm-directory-fields-phone"> ' .
				'<strong>' . esc_html( $phone_type['value'] ) . '</strong>' .
			'</label>';

			// Open a block-level element.
			$markup .= '<div class="sub-sub">';

			// Open a list.
			$markup .= '<ul>';

			// Show checkboxes for fields.
			foreach ( $phone_fields['values'] as $phone_field ) {

				// Skip if not a field we want to render.
				if ( ! in_array( $phone_field['name'], $fields ) ) {
					continue;
				}

				// Open a list item.
				$markup .= '<li>';

				// Is it checked?
				$checked = '';
				if (
					isset( $contact_fields[ $type ]['phone'][ $phone_type['key'] ] ) &&
					in_array( $phone_field['name'], $contact_fields[ $type ]['phone'][ $phone_type['key'] ] )
				) {
					$checked = ' checked="checked"';
				}

				// Show checkbox.
				$markup .= '<label>' .
					'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][phone][' . esc_attr( $phone_type['key'] ) . '][]" value="' . esc_attr( $phone_field['name'] ) . '"' . $checked . '> ' .
					'<strong>' . esc_html( $phone_field['title'] ) . '</strong>' .
				'</label>';

				// Close list item.
				$markup .= '</li>';

			}

			// Close list.
			$markup .= '</ul>';

			// Close block-level element.
			$markup .= '</div>';

			// Close list item.
			$markup .= '</li>';

		}

		// Close list.
		$markup .= '</ul>';

		// --<
		return $markup;

	}

	/**
	 * Construct UI for the Website field.
	 *
	 * @since 0.2
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @param array $contact_fields The chosen fields stored in the post meta.
	 * @param str $type The contact type for which fields should be retrieved.
	 * @return str The constructed field.
	 */
	public function field_website_get( $post, $contact_fields = [], $type = 'Individual' ) {

		// Init return.
		$markup = '';

		// Get all website types.
		$website_types = civicrm_api( 'Website', 'getoptions', [
			'version' => 3,
			'sequential' => 1,
			'field' => 'website_type_id',
		]);

		// Open a list.
		$markup .= '<ul>';

		// We need a checkbox for each type.
		foreach ( $website_types['values'] as $website_type ) {

			// Open a list item.
			$markup .= '<li>';

			// Is it checked?
			$checked = '';
			if (
				isset( $contact_fields[ $type ]['website']['enabled'] ) &&
				in_array( $website_type['key'], $contact_fields[ $type ]['website']['enabled'] )
			) {
				$checked = ' checked="checked"';
			}

			// Show checkbox.
			$markup .= '<label>' .
				'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][website][enabled][]" value="' . esc_attr( $website_type['key'] ) . '"' . $checked . '> ' .
				'<strong>' . esc_html( $website_type['value'] ) . '</strong>' .
			'</label>';

			// Close list item.
			$markup .= '</li>';

		}

		// Close list.
		$markup .= '</ul>';

		// --<
		return $markup;

	}

	/**
	 * Construct UI for the Email field.
	 *
	 * @since 0.2
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @param array $contact_fields The chosen fields stored in the post meta.
	 * @param str $type The contact type for which fields should be retrieved.
	 * @return str The constructed field.
	 */
	public function field_email_get( $post, $contact_fields = [], $type = 'Individual' ) {

		// Init return.
		$markup = '';

		// Get all email types.
		$email_types = civicrm_api( 'Email', 'getoptions', [
			'version' => 3,
			'sequential' => 1,
			'field' => 'location_type_id',
		]);

		// Get all email fields.
		$email_fields = civicrm_api( 'Email', 'getfields', [
			'version' => 3,
			'sequential' => 1,
		]);

		// The fields we want to render.
		$fields = [
			'email',
		];

		// Open a list.
		$markup .= '<ul>';

		// We need a checkbox for each type.
		foreach ( $email_types['values'] as $email_type ) {

			// Open a list item.
			$markup .= '<li>';

			// Is it checked?
			$checked = '';
			if (
				isset( $contact_fields[ $type ]['email']['enabled'] ) &&
				in_array( $email_type['key'], $contact_fields[ $type ]['email']['enabled'] )
			) {
				$checked = ' checked="checked"';
			}

			// Show checkbox.
			$markup .= '<label>' .
				'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][email][enabled][]" value="' . esc_attr( $email_type['key'] ) . '"' . $checked . '> ' .
				'<strong>' . esc_html( $email_type['value'] ) . '</strong>' .
			'</label>';

			// Close list item.
			$markup .= '</li>';

		}

		// Close list.
		$markup .= '</ul>';

		// --<
		return $markup;

	}

	// #########################################################################

	/**
	 * Get the contact fields data for a directory.
	 *
	 * @since 0.2.2
	 *
	 * @param integer $post_id The ID of the post.
	 * @return array $contact_fields The contact fields data for the post.
	 */
	public function contact_fields_get( $post_id = null ) {

		// Use current post if none passed.
		if ( is_null( $post_id ) ) {
			$post_id = get_the_ID();
		}

		// Set key.
		$db_key = '_' . $this->contact_fields_meta_key;

		// Default to empty.
		$contact_fields = [];

		// Get value if the custom field already has one.
		$existing = get_post_meta( $post_id, $db_key, true );
		if ( ! empty( $existing ) && is_array( $existing ) ) {
			$contact_fields = get_post_meta( $post_id, $db_key, true );
		}

		// --<
		return $contact_fields;

	}

	/**
	 * Get the contact types for a directory.
	 *
	 * @since 0.2.4
	 *
	 * @param integer $post_id The ID of the post.
	 * @return array $contact_types The contact types data for the post.
	 */
	public function contact_types_get( $post_id = null ) {

		// Use current post if none passed.
		if ( is_null( $post_id ) ) {
			$post_id = get_the_ID();
		}

		// Set key.
		$db_key = '_' . $this->contact_types_meta_key;

		// Default to empty.
		$contact_types = [];

		// Get value if the custom type already has one.
		$existing = get_post_meta( $post_id, $db_key, true );
		if ( ! empty( $existing ) && is_array( $existing ) ) {
			$contact_types = get_post_meta( $post_id, $db_key, true );
		}

		// --<
		return $contact_types;

	}

	/**
	 * Stores our additional params.
	 *
	 * @since 0.1
	 *
	 * @param integer $post_id The ID of the post or revision.
	 * @param integer $post The post object.
	 */
	public function save_post( $post_id, $post ) {

		// If no post, kick out.
		if ( ! $post ) {
			return;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_page', $post->ID ) ) {
			return;
		}

		// Bail if not our post type.
		if ( $post->post_type != $this->post_type_name ) {
			return;
		}

		// Check for revision.
		if ( $post->post_type == 'revision' ) {

			// Get parent.
			if ( $post->post_parent != 0 ) {
				$post_obj = get_post( $post->post_parent );
			} else {
				$post_obj = $post;
			}

		} else {
			$post_obj = $post;
		}

		// Store our CiviCRM Group ID metadata.
		$this->save_group_id_meta( $post_obj );

		// Store our Configuration metadata.
		$this->save_config_meta( $post_obj );

		// Authenticate before proceeding.
		$nonce = isset( $_POST['civicrm_directory_contacts_nonce'] ) ? $_POST['civicrm_directory_contacts_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'civicrm_directory_contacts_box' ) ) {
			return;
		}

		// Store our CiviCRM Contact Types metadata.
		$this->save_contact_types_meta( $post_obj );

		// Store our CiviCRM Contact Fields metadata.
		$this->save_contact_fields_meta( $post_obj );

	}

	/**
	 * When a post is saved, this also saves the metadata.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the post.
	 */
	private function save_group_id_meta( $post ) {

		// Authenticate.
		$nonce = isset( $_POST['civicrm_directory_group_id_nonce'] ) ? $_POST['civicrm_directory_group_id_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'civicrm_directory_group_id_box' ) ) {
			return;
		}

		// Define key.
		$db_key = '_' . $this->group_id_meta_key;

		// Get value.
		$value = isset( $_POST[ $this->group_id_meta_key ] ) ? absint( $_POST[ $this->group_id_meta_key ] ) : 0;

		// Save for this post.
		$this->save_meta( $post, $db_key, $value );

	}

	/**
	 * When a post is saved, this also saves the Configuration metadata.
	 *
	 * @since 0.2.4
	 *
	 * @param WP_Post $post The object for the post.
	 */
	private function save_config_meta( $post ) {

		// Authenticate.
		$nonce = isset( $_POST['civicrm_directory_config_nonce'] ) ? $_POST['civicrm_directory_config_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'civicrm_directory_config_box' ) ) {
			return;
		}

		// Define key.
		$db_key = '_' . $this->mapping_meta_key;

		// Get value.
		$value = isset( $_POST[ $this->mapping_meta_key ] ) ? absint( $_POST[ $this->mapping_meta_key ] ) : 0;

		// Save for this post.
		$this->save_meta( $post, $db_key, $value );

		// Define key.
		$db_key = '_' . $this->mapping_height_meta_key;

		// Get height.
		$height = isset( $_POST[ $this->mapping_height_meta_key ] ) ? absint( $_POST[ $this->mapping_height_meta_key ] ) : 0;

		// Handle empty values by substituting default.
		if ( empty( $height ) || $height === 0 ) {
			$height = $this->plugin->admin->setting_get( 'google_maps_height' );
		}

		// Save for this post.
		$this->save_meta( $post, $db_key, $height );

		// Define key.
		$db_key = '_' . $this->letter_meta_key;

		// Get value.
		$value = isset( $_POST[ $this->letter_meta_key ] ) ? absint( $_POST[ $this->letter_meta_key ] ) : 0;

		// Save for this post.
		$this->save_meta( $post, $db_key, $value );

		// Define key.
		$db_key = '_' . $this->search_meta_key;

		// Get value.
		$value = isset( $_POST[ $this->search_meta_key ] ) ? absint( $_POST[ $this->search_meta_key ] ) : 0;

		// Save for this post.
		$this->save_meta( $post, $db_key, $value );

	}

	/**
	 * When a post is saved, this also saves the Contact Type metadata.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the post.
	 */
	private function save_contact_types_meta( $post ) {

		// Define key.
		$db_key = '_' . $this->contact_types_meta_key;

		// Init as empty.
		$contact_types = [];

		// Which post types are we enabling the CiviCRM button on?
		if (
			isset( $_POST[ $this->contact_types_meta_key ] ) &&
			count( $_POST[ $this->contact_types_meta_key ] ) > 0
		) {

			// Grab the array.
			$contact_types = $_POST[ $this->contact_types_meta_key ];

			// Sanitise it.
			array_walk(
				$contact_types,
				function( &$item ) {
					$item = sanitize_text_field( trim( $item ) );
				}
			);

		}

		// Save for this post.
		$this->save_meta( $post, $db_key, $contact_types );

	}

	/**
	 * When a post is saved, this also saves the Contact Fields metadata.
	 *
	 * The array is keyed by Contact Type, then by 'core', 'custom', etc - e.g.
	 *
	 * array(
	 *     'Individual' => array(
	 *        'core' => array( 'first_name', 'last_name' ),
	 *        'custom' => array( 'custom_3', 'custom_4' )
	 *     ),
	 *     'Organization' => array(
	 *         'core' => array( 'organization_name' ),
	 *         'custom' => array( 'custom_1' )
	 *     ),
	 * )
	 *
	 * @since 0.1.3
	 *
	 * @param WP_Post $post The object for the post.
	 */
	private function save_contact_fields_meta( $post ) {

		// Define key.
		$db_key = '_' . $this->contact_fields_meta_key;

		// Init as empty.
		$contact_fields = [];

		// Sanity checks.
		if ( ! isset( $_POST[ $this->contact_fields_meta_key ] ) ) {
			return;
		}
		if ( empty( $_POST[ $this->contact_fields_meta_key ] ) ) {
			return;
		}
		if ( ! is_array( $_POST[ $this->contact_fields_meta_key ] ) ) {
			return;
		}

		// Grab the array.
		$contact_fields = $_POST[ $this->contact_fields_meta_key ];

		// Sanitise array keys.
		$new_array = [];
		foreach ( $contact_fields as $key => $sub_array ) {
			$new_key = ucFirst( sanitize_text_field( trim( $key ) ) );
			$new_array[ $new_key ] = $sub_array;
		}

		// Parse nested arrays.
		foreach ( $new_array as $sub_array ) {

			// Sanitise sub-array.
			foreach ( $sub_array as $data_array ) {
				array_walk(
					$data_array,
					function( &$item ) {
						if ( is_string( $item ) ) {
							$item = sanitize_text_field( trim( $item ) );
						}
						if ( is_array( $item ) ) {
							array_walk(
								$item,
								function( &$entry ) {
									if ( is_string( $entry ) ) {
										$entry = sanitize_text_field( trim( $entry ) );
									}
								}
							);
						}
					}
				);
			}

		}

		// Save for this post.
		$this->save_meta( $post, $db_key, $new_array );

	}

	/**
	 * Utility to automate metadata saving.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The WordPress post object.
	 * @param string $key The meta key.
	 * @param mixed $data The data to be saved.
	 * @return mixed $data The data that was saved.
	 */
	private function save_meta( $post, $key, $data = '' ) {

		// If the custom field already has a value.
		$existing = get_post_meta( $post->ID, $key, true );
		if ( false !== $existing ) {

			// Update the data.
			update_post_meta( $post->ID, $key, $data );

		} else {

			// Add the data.
			add_post_meta( $post->ID, $key, $data, true );

		}

		// --<
		return $data;

	}

}
