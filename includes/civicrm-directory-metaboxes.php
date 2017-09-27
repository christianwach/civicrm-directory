<?php

/**
 * CiviCRM Directory Metaboxes Class.
 *
 * A class that encapsulates all Metaboxes for CiviCRM Directory.
 *
 * @package CiviCRM_Directory
 */
class CiviCRM_Directory_Metaboxes {

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
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param object $parent The parent object.
	 */
	public function __construct( $parent ) {

		// store
		$this->plugin = $parent;

	}



	/**
	 * Register WordPress hooks.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// add meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// intercept save
		add_action( 'save_post', array( $this, 'save_post' ), 1, 2 );

	}




	// #########################################################################



	/**
	 * Adds metaboxes to admin screens.
	 *
	 * @since 0.1
	 */
	public function add_meta_boxes() {

		// add our Config meta box
		add_meta_box(
			'civicrm_directory_contact_types',
			__( 'Directory Configuration', 'civicrm-directory' ),
			array( $this, 'config_metabox' ),
			$this->post_type_name,
			'normal', // column: options are 'normal' and 'side'
			'core' // vertical placement: options are 'core', 'high', 'low'
		);

		// add our Group ID meta box
		add_meta_box(
			'civicrm_directory_group_id',
			__( 'CiviCRM Group', 'civicrm-directory' ),
			array( $this, 'group_id_metabox' ),
			$this->post_type_name,
			'normal', // column: options are 'normal' and 'side'
			'core' // vertical placement: options are 'core', 'high', 'low'
		);

	}



	/**
	 * Adds a metabox to CPT edit screens for Configuration.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
	public function config_metabox( $post ) {

		// sanity check
		if ( ! ( $post instanceof WP_Post ) ) return;

		// Use nonce for verification
		wp_nonce_field( 'civicrm_directory_config_box', 'civicrm_directory_config_nonce' );

		// ---------------------------------------------------------------------

		// set key
		$db_key = '_' . $this->contact_types_meta_key;

		// default to empty
		$contact_types = array();

		// get value if the custom field already has one
		$existing = get_post_meta( $post->ID, $db_key, true );
		if ( ! empty( $existing ) ) {
			$contact_types = get_post_meta( $post->ID, $db_key, true );
		}

		// get all contact types
		$all_contact_types = $this->plugin->civi->contact_types_get();

		// instructions
		echo '<p>' . __( 'Choose the kinds of CiviCRM Contact Types for the Contacts in this Directory. This is useful because if, for example, you know that all of the Contacts in this Directory will be Organisations then it makes searching the Directory more efficient. You can change this setting if you need to.', 'civicrm-directory' ) . '</p>';

		// open a list
		echo '<ul>';

		// show checkboxes for each contact type
		foreach( $all_contact_types AS $contact_type ) {

			// is it checked?
			$checked = '';
			if ( in_array( $contact_type['name'], $contact_types ) ) {
				$checked = ' checked="checked"';
			}

			//  show checkbox
			echo '<li>' .
					'<label>' .
						'<input type="checkbox" name="' . $this->contact_types_meta_key . '[]" value="' . esc_attr( $contact_type['name'] ) . '" class="civicrm-directory-types ' . $this->contact_types_meta_key . '-' . esc_attr( $contact_type['name'] ) . '"' . $checked . '> ' .
						'<strong>' . esc_html( $contact_type['name'] ) . '</strong>' .
					'</label>' .
				 '</li>';

		}

		// close list
		echo '</ul>';

		// ---------------------------------------------------------------------

		// get contact fields data
		$contact_fields = $this->contact_fields_get( $post->ID );

		// let's have some style
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

		// open div
		echo '<div class="civicrm-directory-fields civicrm-directory-Individual">';

		// show header
		echo '<h3>' . __( 'Fields for Individuals', 'civicrm-directory' ) . '</h3>';

		// open fields container
		echo '<div class="civicrm-directory-fields-container">';

		// print Individual fields
		$this->fields_core_render( $post, $contact_fields, 'Individual' );

		// print Individual custom fields
		$this->fields_custom_render( $post, $contact_fields, 'Individual' );

		// print Individual other fields
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
		do_action( 'config_metabox_fields', $post, $contact_fields, 'Individual' );

		// close div
		echo '</div>';

		// close div
		echo '</div>';

		// open div
		echo '<div class="civicrm-directory-fields civicrm-directory-Household">';

		// show header
		echo '<h3>' . __( 'Fields for Households', 'civicrm-directory' ) . '</h3>';

		// open fields container
		echo '<div class="civicrm-directory-fields-container">';

		// print Household fields
		$this->fields_core_render( $post, $contact_fields, 'Household' );

		// print Household custom fields
		$this->fields_custom_render( $post, $contact_fields, 'Household' );

		// print Household other fields
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
		do_action( 'config_metabox_fields', $post, $contact_fields, 'Household' );

		// close div
		echo '</div>';

		// close div
		echo '</div>';

		// open div
		echo '<div class="civicrm-directory-fields civicrm-directory-Organization">';

		// show header
		echo '<h3>' . __( 'Fields for Organizations', 'civicrm-directory' ) . '</h3>';

		// open fields container
		echo '<div class="civicrm-directory-fields-container">';

		// print Organization fields
		$this->fields_core_render( $post, $contact_fields, 'Organization' );

		// print Organization custom fields
		$this->fields_custom_render( $post, $contact_fields, 'Organization' );

		// print Organization other fields
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
		do_action( 'config_metabox_fields', $post, $contact_fields, 'Organization' );

		// close div
		echo '</div>';

		// close div
		echo '</div>';

		// ---------------------------------------------------------------------

		// add our metabox javascript in the footer
		wp_enqueue_script(
			'civicrm_directory_config_box_js',
			CIVICRM_DIRECTORY_URL . '/assets/js/civicrm-directory-config-box.js',
			array( 'jquery' ),
			CIVICRM_DIRECTORY_VERSION,
			true
		);

		// init localisation
		$localisation = array(
		);

		// init settings
		$settings = array(
		);

		// localisation array
		$vars = array(
			'localisation' => $localisation,
			'settings' => $settings,
		);

		// localise
		wp_localize_script(
			'civicrm_directory_config_box_js',
			'CiviCRM_Directory_Config_Box_Settings',
			$vars
		);

	}



	/**
	 * Adds a metabox to CPT edit screens for CiviCRM Group ID.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
	public function group_id_metabox( $post ) {

		// Use nonce for verification
		wp_nonce_field( 'civicrm_directory_group_id_box', 'civicrm_directory_group_id_nonce' );

		// set key
		$db_key = '_' . $this->group_id_meta_key;

		// default to empty
		$group_id = '';

		// get value if the custom field already has one
		$existing = get_post_meta( $post->ID, $db_key, true );
		if ( false !== $existing ) {
			$group_id = get_post_meta( $post->ID, $db_key, true );
		}

		// instructions
		echo '<p>' . __( 'Choose the CiviCRM Group to which all Contacts for this Directory belong.', 'civicrm-directory' ) . '</p>';

		// start with empty option
		$selected = empty( $group_id ) ? ' selected="selected"' : '';
		$options = '<option value=""' . $selected . '>' . __( '- Select a Group -', 'civicrm-directory' ) . '</option>';

		// get CiviCRM groups that could be Directories
		$groups = $this->plugin->civi->groups_get();

		// add CiviCRM groups
		foreach( $groups AS $key => $data ) {
			$selected = ( $key == $group_id ) ? ' selected="selected"' : '';
			$options .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $data['title'] ) . '</option>';
		}

		// show the dropdown
		echo '<p><select name="' . $this->group_id_meta_key . '" id="' . $this->group_id_meta_key . '">' .
				$options .
			 '</select></p>';

	}



	// #########################################################################



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
	public function fields_core_render( $post, $contact_fields = array(), $type = 'Individual' ) {

		// init types array
		$types = array( 'Contact' );

		// add passed in type
		$types[] = $type;

		// get all public contact fields
		$all_contact_fields = $this->plugin->civi->contact_fields_get( $types, 'public' );

		// bail if we get none
		if ( count( $all_contact_fields ) === 0 ) return false;

		// sep
		echo '<hr class="sep-core">';

		// let's have a heading
		echo '<h4>' . __( 'Core Fields', 'civicrm-directory' ) . '</h4>';

		// open a list
		echo '<ul>';

		// show checkboxes for each contact type
		foreach( $all_contact_fields AS $contact_field ) {

			// is it checked?
			$checked = '';
			if (
				isset( $contact_fields[$type]['core'] ) AND
				in_array( $contact_field['name'], $contact_fields[$type]['core'] )
			) {
				$checked = ' checked="checked"';
			}

			//  show checkbox
			echo '<li>' .
					'<label>' .
						'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][core][]" value="' . esc_attr( $contact_field['name'] ) . '"' . $checked . '> ' .
						'<strong>' . esc_html( $contact_field['title'] ) . '</strong>' .
					'</label>' .
				 '</li>';

		}

		// close list
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
	public function fields_custom_render( $post, $contact_fields = array(), $type = 'Individual' ) {

		// init types array
		$types = array( 'Contact' );

		// add passed in type
		$types[] = $type;

		// get all contact custom fields
		$all_contact_custom_fields = $this->plugin->civi->contact_custom_fields_get( $types );

		// bail if we get none
		if ( count( $all_contact_custom_fields ) === 0 ) return false;

		// extract just the data we need
		$custom_fields = array();
		foreach( $all_contact_custom_fields as $key => $value ) {
			$custom_fields[$value['id']] = $value['label'];
		}

		// sep
		echo '<hr>';

		// let's have a heading
		echo '<h4>' . __( 'Custom Fields', 'civicrm-directory' ) . '</h4>';

		// open a list
		echo '<ul>';

		// show checkboxes for each contact type
		foreach( $custom_fields AS $key => $title ) {

			// is it checked?
			$checked = '';
			if (
				isset( $contact_fields[$type]['custom'] ) AND
				in_array( $key, $contact_fields[$type]['custom'] )
			) {
				$checked = ' checked="checked"';
			}

			//  show checkbox
			echo '<li>' .
					'<label>' .
						'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][custom][]" value="' . esc_attr( $key ) . '"' . $checked . '> ' .
						'<strong>' . esc_html( $title ) . '</strong>' .
					'</label>' .
				 '</li>';

		}

		// close list
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
	public function fields_other_render( $post, $contact_fields = array(), $type = 'Individual' ) {

		// what do we want?
		$other_fields = array(
			'address' => __( 'Address', 'civicrm-directory' ),
			'phone' => __( 'Phone', 'civicrm-directory' ),
			'website' => __( 'Website', 'civicrm-directory' ),
			'email' => __( 'Email', 'civicrm-directory' ),
		);

		// sep
		echo '<hr>';

		// let's have a heading
		echo '<h4>' . __( 'Other Fields', 'civicrm-directory' ) . '</h4>';

		// open a list
		echo '<ul>';

		// show UI for each field type
		foreach( $other_fields AS $key => $title ) {

			// open list item
			echo '<li>';

			// is it checked?
			$checked = '';
			if (
				isset( $contact_fields[$type]['other'] ) AND
				in_array( $key, $contact_fields[$type]['other'] )
			) {
				$checked = ' checked="checked"';
			}

			//  show checkbox
			echo '<label>' .
					'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][other][]" value="' . esc_attr( $key ) . '"' . $checked . ' class="civicrm-directory-fields-other"> ' .
					'<strong>' . esc_html( $title ) . '</strong>' .
				'</label>';

			// open a block-level element
			echo '<div class="sub">';

			// switch by key
			switch( $key ) {

				case 'address' :
					echo $this->field_address_get( $post, $contact_fields, $type );
					break;

				case 'phone' :
					echo $this->field_phone_get( $post, $contact_fields, $type );
					break;

				case 'website' :
					echo $this->field_website_get( $post, $contact_fields, $type );
					break;

				case 'email' :
					echo $this->field_email_get( $post, $contact_fields, $type );
					break;

			}

			// close block-level element
			echo '</div>';

			// close list item
			echo '</li>';

		}

		// close list
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
	public function field_address_get( $post, $contact_fields = array(), $type = 'Individual' ) {

		// init return
		$markup = '';

		// get address types
		$address_types = civicrm_api( 'Address', 'getoptions', array(
			'version' => 3,
			'sequential' => 1,
			'field' => 'location_type_id',
		));

		// the fields we want to render
		$fields = array(
			'street_address',
			'supplemental_address_1',
			'supplemental_address_2',
			'city',
			'state_province_id',
			'postal_code',
			'country_id',
		);

		// get all address fields
		$address_fields = civicrm_api( 'Address', 'getfields', array(
			'version' => 3,
			'sequential' => 1,
		));

		// open a list
		$markup .= '<ul>';

		// we need a checkbox for each type
		foreach( $address_types['values'] AS $address_type ) {

			// open a list item
			$markup .= '<li>';

			// is it checked?
			$checked = '';
			if (
				isset( $contact_fields[$type]['address']['enabled'] ) AND
				in_array( $address_type['key'], $contact_fields[$type]['address']['enabled'] )
			) {
				$checked = ' checked="checked"';
			}

			//  show checkbox
			$markup .= '<label>' .
				'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][address][enabled][]" value="' . esc_attr( $address_type['key'] ) . '"' . $checked . ' class="civicrm-directory-fields-address"> ' .
				'<strong>' . esc_html( $address_type['value'] ) . '</strong>' .
			'</label>';

			// open a block-level element
			$markup .= '<div class="sub-sub">';

			// open a list
			$markup .= '<ul>';

			// show checkboxes for fields
			foreach( $address_fields['values'] AS $address_field ) {

				// open a list item
				$markup .= '<li>';

				// skip if not a field we want to render
				if ( ! in_array( $address_field['name'], $fields ) ) continue;

				// is it checked?
				$checked = '';
				if (
					isset( $contact_fields[$type]['address'][$address_type['key']] ) AND
					in_array( $address_field['name'], $contact_fields[$type]['address'][$address_type['key']] )
				) {
					$checked = ' checked="checked"';
				}

				//  show checkbox
				$markup .= '<label>' .
					'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][address][' . $address_type['key'] . '][]" value="' . esc_attr( $address_field['name'] ) . '"' . $checked . '> ' .
					'<strong>' . esc_html( $address_field['title'] ) . '</strong>' .
				'</label>';

				// close list item
				$markup .= '</li>';

			}

			// close list
			$markup .= '</ul>';

			// close block-level element
			$markup .= '</div>';

			// close list item
			$markup .= '</li>';

		}

		// close list
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
	public function field_phone_get( $post, $contact_fields = array(), $type = 'Individual' ) {

		// init return
		$markup = '';

		// get all phone types
		$phone_types = civicrm_api( 'Phone', 'getoptions', array(
			'version' => 3,
			'sequential' => 1,
			'field' => 'location_type_id',
		));

		// get all phone fields
		$phone_fields = civicrm_api( 'Phone', 'getfields', array(
			'version' => 3,
			'sequential' => 1,
		));

		// the fields we want to render
		$fields = array(
			'phone',
			'phone_type_id',
		);

		// open a list
		$markup .= '<ul>';

		// we need a checkbox for each type
		foreach( $phone_types['values'] AS $phone_type ) {

			// open a list item
			$markup .= '<li>';

			// is it checked?
			$checked = '';
			if (
				isset( $contact_fields[$type]['phone']['enabled'] ) AND
				in_array( $phone_type['key'], $contact_fields[$type]['phone']['enabled'] )
			) {
				$checked = ' checked="checked"';
			}

			//  show checkbox
			$markup .= '<label>' .
				'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][phone][enabled][]" value="' . esc_attr( $phone_type['key'] ) . '"' . $checked . ' class="civicrm-directory-fields-phone"> ' .
				'<strong>' . esc_html( $phone_type['value'] ) . '</strong>' .
			'</label>';

			// open a block-level element
			$markup .= '<div class="sub-sub">';

			// open a list
			$markup .= '<ul>';

			// show checkboxes for fields
			foreach( $phone_fields['values'] AS $phone_field ) {

				// skip if not a field we want to render
				if ( ! in_array( $phone_field['name'], $fields ) ) continue;

				// open a list item
				$markup .= '<li>';

				// is it checked?
				$checked = '';
				if (
					isset( $contact_fields[$type]['phone'][$phone_type['key']] ) AND
					in_array( $phone_field['name'], $contact_fields[$type]['phone'][$phone_type['key']] )
				) {
					$checked = ' checked="checked"';
				}

				//  show checkbox
				$markup .= '<label>' .
					'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][phone][' . esc_attr( $phone_type['key'] ) . '][]" value="' . esc_attr( $phone_field['name'] ) . '"' . $checked . '> ' .
					'<strong>' . esc_html( $phone_field['title'] ) . '</strong>' .
				'</label>';

				// close list item
				$markup .= '</li>';

			}

			// close list
			$markup .= '</ul>';

			// close block-level element
			$markup .= '</div>';

			// close list item
			$markup .= '</li>';

		}

		// close list
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
	public function field_website_get( $post, $contact_fields = array(), $type = 'Individual' ) {

		// init return
		$markup = '';

		// get all website types
		$website_types = civicrm_api( 'Website', 'getoptions', array(
			'version' => 3,
			'sequential' => 1,
			'field' => "website_type_id",
		));

		// open a list
		$markup .= '<ul>';

		// we need a checkbox for each type
		foreach( $website_types['values'] AS $website_type ) {

			// open a list item
			$markup .= '<li>';

			// is it checked?
			$checked = '';
			if (
				isset( $contact_fields[$type]['website']['enabled'] ) AND
				in_array( $website_type['key'], $contact_fields[$type]['website']['enabled'] )
			) {
				$checked = ' checked="checked"';
			}

			//  show checkbox
			$markup .= '<label>' .
				'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][website][enabled][]" value="' . esc_attr( $website_type['key'] ) . '"' . $checked . '> ' .
				'<strong>' . esc_html( $website_type['value'] ) . '</strong>' .
			'</label>';

			// close list item
			$markup .= '</li>';

		}

		// close list
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
	public function field_email_get( $post, $contact_fields = array(), $type = 'Individual' ) {

		// init return
		$markup = '';

		// get all email types
		$email_types = civicrm_api( 'Email', 'getoptions', array(
			'version' => 3,
			'sequential' => 1,
			'field' => "location_type_id",
		));

		// get all email fields
		$email_fields = civicrm_api( 'Email', 'getfields', array(
			'version' => 3,
			'sequential' => 1,
		));

		// the fields we want to render
		$fields = array(
			'email',
		);

		// open a list
		$markup .= '<ul>';

		// we need a checkbox for each type
		foreach( $email_types['values'] AS $email_type ) {

			// open a list item
			$markup .= '<li>';

			// is it checked?
			$checked = '';
			if (
				isset( $contact_fields[$type]['email']['enabled'] ) AND
				in_array( $email_type['key'], $contact_fields[$type]['email']['enabled'] )
			) {
				$checked = ' checked="checked"';
			}

			//  show checkbox
			$markup .= '<label>' .
				'<input type="checkbox" name="' . $this->contact_fields_meta_key . '[' . strtolower( $type ) . '][email][enabled][]" value="' . esc_attr( $email_type['key'] ) . '"' . $checked . '> ' .
				'<strong>' . esc_html( $email_type['value'] ) . '</strong>' .
			'</label>';

			// close list item
			$markup .= '</li>';

		}

		// close list
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

		// use current post if none passed
		if ( is_null( $post_id ) ) $post_id = get_the_ID();

		// set key
		$db_key = '_' . $this->contact_fields_meta_key;

		// default to empty
		$contact_fields = array();

		// get value if the custom field already has one
		$existing = get_post_meta( $post_id, $db_key, true );
		if ( ! empty( $existing ) ) {
			$contact_fields = get_post_meta( $post_id, $db_key, true );
		}

		// --<
		return $contact_fields;

	}



	/**
	 * Stores our additional params.
	 *
	 * @since 0.1
	 *
	 * @param integer $post_id the ID of the post (or revision)
	 * @param integer $post the post object
	 */
	public function save_post( $post_id, $post ) {

		// if no post, kick out
		if ( ! $post ) return;

		// is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) AND DOING_AUTOSAVE ) return;

		// check permissions
		if ( ! current_user_can( 'edit_page', $post->ID ) ) return;

		// bail if not our post type
		if ( $post->post_type != $this->post_type_name ) return;

		// check for revision
		if ( $post->post_type == 'revision' ) {

			// get parent
			if ( $post->post_parent != 0 ) {
				$post_obj = get_post( $post->post_parent );
			} else {
				$post_obj = $post;
			}

		} else {
			$post_obj = $post;
		}

		// store our CiviCRM Group ID metadata
		$this->save_group_id_meta( $post_obj );

		// authenticate before proceeding
		$nonce = isset( $_POST['civicrm_directory_config_nonce'] ) ? $_POST['civicrm_directory_config_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'civicrm_directory_config_box' ) ) return;

		// store our CiviCRM Contact Types metadata
		$this->save_contact_types_meta( $post_obj );

		// store our CiviCRM Contact Fields metadata
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

		// authenticate
		$nonce = isset( $_POST['civicrm_directory_group_id_nonce'] ) ? $_POST['civicrm_directory_group_id_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'civicrm_directory_group_id_box' ) ) return;

		// define key
		$db_key = '_' . $this->group_id_meta_key;

		// get value
		$value = isset( $_POST[$this->group_id_meta_key] ) ? absint( $_POST[$this->group_id_meta_key] ) : 0;

		// save for this post
		$this->_save_meta( $post, $db_key, $value );

	}



	/**
	 * When a post is saved, this also saves the Contact Type metadata.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the post.
	 */
	private function save_contact_types_meta( $post ) {

		// define key
		$db_key = '_' . $this->contact_types_meta_key;

		// init as empty
		$contact_types = array();

		// which post types are we enabling the CiviCRM button on?
		if (
			isset( $_POST[$this->contact_types_meta_key] ) AND
			count( $_POST[$this->contact_types_meta_key] ) > 0
		) {

			// grab the array
			$contact_types = $_POST[$this->contact_types_meta_key];

			// sanitise it
			array_walk(
				$contact_types,
				function( &$item ) {
					$item = sanitize_text_field( trim( $item ) );
				}
			);

		}

		// save for this post
		$this->_save_meta( $post, $db_key, $contact_types );

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

		// define key
		$db_key = '_' . $this->contact_fields_meta_key;

		// init as empty
		$contact_fields = array();

		// sanity checks
		if ( ! isset( $_POST[$this->contact_fields_meta_key] ) ) return;
		if ( empty( $_POST[$this->contact_fields_meta_key] ) ) return;
		if ( ! is_array( $_POST[$this->contact_fields_meta_key] ) ) return;

		// grab the array
		$contact_fields = $_POST[$this->contact_fields_meta_key];

		// sanitise array keys
		$new_array = array();
		foreach( $contact_fields AS $key => $sub_array ) {
			$new_key = ucFirst( sanitize_text_field( trim( $key ) ) );
			$new_array[$new_key] = $sub_array;
		}

		// parse nested arrays
		foreach( $new_array AS $sub_array ) {

			// sanitise sub-array
			foreach( $sub_array AS $data_array ) {
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

		// save for this post
		$this->_save_meta( $post, $db_key, $new_array );

	}



	/**
	 * Utility to automate metadata saving.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post_obj The WordPress post object.
	 * @param string $key The meta key.
	 * @param mixed $data The data to be saved.
	 * @return mixed $data The data that was saved.
	 */
	private function _save_meta( $post, $key, $data = '' ) {

		// if the custom field already has a value
		$existing = get_post_meta( $post->ID, $key, true );
		if ( false !== $existing ) {

			// update the data
			update_post_meta( $post->ID, $key, $data );

		} else {

			// add the data
			add_post_meta( $post->ID, $key, $data, true );

		}

		// --<
		return $data;

	}



} // class ends



