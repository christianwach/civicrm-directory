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
	 * @var object $cpt The name of the Custom Post Type.
	 */
	public $post_type_name = 'directory';

	/**
	 * CiviCRM Contact Types meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var str
	 */
	public $contact_types_meta_key = 'civicrm_directory_contact_types';

	/**
	 * CiviCRM Group ID meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var str
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

		// Use nonce for verification
		wp_nonce_field( 'civicrm_directory_config_box', 'civicrm_directory_config_nonce' );

		// set key
		$db_key = '_' . $this->contact_types_meta_key;

		// default to empty
		$contact_types = array();

		// get value if the custom field already has one
		$existing = get_post_meta( $post->ID, $db_key, true );
		if ( ! empty( $existing ) ) {
			$contact_types = get_post_meta( $post->ID, $db_key, true );
		}

		/*
		error_log( print_r( array(
			'method' => __METHOD__,
			'contact_types' => $contact_types,
		), true ) );
		*/

		// get all contact types
		$all_contact_types = $this->plugin->admin->contact_types_get();

		// instructions
		echo '<p>' . __( 'Choose the kinds of CiviCRM Contact Types for the Contacts in this Directory. This is useful because if, for example, you know that all of the Contacts in this Directory will be Organisations then it makes searching the Directory more efficient. You can change this setting if you need to.', 'civicrm-directory' ) . '</p>';

		// open a list
		echo '<ul>';

		// show checkboxes for each contact type
		foreach( $all_contact_types AS $contact_type ) {

			// is it checked?
			$checked = '';
			if ( in_array( $contact_type['id'], $contact_types ) ) {
				$checked = ' checked="checked"';
			}

			//  show checkbox
			echo '<li>' .
					'<label>' .
						'<input type="checkbox" name="' . $this->contact_types_meta_key . '[]" value="' . esc_attr( $contact_type['id'] ) . '"' . $checked . '> ' .
						'<strong>' . esc_html( $contact_type['name'] ) . '</strong>' .
					'</label>' .
				 '</li>';

		}

		// close list
		echo '<ul>';

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
		$groups = $this->plugin->admin->groups_get();

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

		// store our CiviCRM Contact Types metadata
		$this->save_contact_types_meta( $post_obj );

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

		// authenticate
		$nonce = isset( $_POST['civicrm_directory_config_nonce'] ) ? $_POST['civicrm_directory_config_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'civicrm_directory_config_box' ) ) return;

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
					$item = absint( trim( $item ) );
				}
			);

		}

		// save for this post
		$this->_save_meta( $post, $db_key, $contact_types );

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




} // class CiviCRM_Directory_Metaboxes ends


