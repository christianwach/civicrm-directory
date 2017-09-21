<?php

/**
 * CiviCRM Directory CiviCRM Class.
 *
 * A class that encapsulates functionality that interacts with CiviCRM.
 *
 * @since 0.1.2
 */
class CiviCRM_Directory_Civi {

	/**
	 * Plugin (calling) object.
	 *
	 * @since 0.1.2
	 * @access public
	 * @var object $plugin The plugin object.
	 */
	public $plugin;

	/**
	 * Common Contact fields to make available on front end.
	 *
	 * @since 0.1.2
	 * @access public
	 * @var array $contact_fields_common The common public contact fields.
	 */
	public $contact_fields_common = array(
		'nick_name',
		'email',
		'source',
	);

	/**
	 * Contact fields to make available on front end.
	 *
	 * @since 0.1.2
	 * @access public
	 * @var array $contact_fields_individual The public contact fields for Individuals.
	 */
	public $contact_fields_individual = array(
		'prefix_id',
		'first_name',
		'last_name',
		'middle_name',
		'suffix_id',
		'job_title',
		'gender_id',
		'birth_date',
		'current_employer',
	);

	/**
	 * Contact fields for Organisations to make available on front end.
	 *
	 * @since 0.1.2
	 * @access public
	 * @var array $contact_fields_organization The public contact fields for Organisations.
	 */
	public $contact_fields_organization = array(
		'legal_name',
		'organization_name',
		'sic_code'
	);

	/**
	 * Contact fields for Households to make available on front end.
	 *
	 * @since 0.1.2
	 * @access public
	 * @var array $contact_fields_household The public contact fields for Households.
	 */
	public $contact_fields_household = array(
		'household_name',
	);



	/**
	 * Constructor.
	 *
	 * @since 0.1.2
	 *
	 * @param object $parent The parent object.
	 */
	public function __construct( $parent ) {

		// store
		$this->plugin = $parent;

	}



	/**
	 * Initialise CiviCRM if necessary.
	 *
	 * @since 0.1.2
	 *
	 * @return bool $initialised True if CiviCRM initialised, false otherwise.
	 */
	public function initialize() {

		// try and init CiviCRM
		if ( ! function_exists( 'civi_wp' ) ) return false;
		if ( ! civi_wp()->initialize() ) return false;

		// --<
		return true;

	}



	//##########################################################################



	/**
	 * Get publicly-viewable CiviCRM groups.
	 *
	 * @since 0.1.2
	 *
	 * @return array $groups The publicly-viewable CiviCRM groups.
	 */
	public function groups_get() {

		// init return
		$groups = array();

		// try and init CiviCRM
		if ( ! $this->initialize() ) return $groups;

		// define params to get all publicly-viewable groups
		$params = array(
			'version' => 3,
			'visibility' => 'Public Pages',
			'is_active' => 1,
			'is_hidden' => 0,
			'options' => array(
				'limit' => '0', // no limit
			),
		);

		// get all groups
		$all_groups = civicrm_api( 'group', 'get', $params );

		// override return if we get some
		if (
			$all_groups['is_error'] == 0 AND
			isset( $all_groups['values'] ) AND
			count( $all_groups['values'] ) > 0
		) {
			$groups = $all_groups['values'];
		}

		/*
		error_log( print_r( array(
			'method' => __METHOD__,
			'params' => $params,
			'groups' => $groups,
		), true ) );
		*/

		// --<
		return $groups;

	}



	//##########################################################################



	/**
	 * Get contacts in a specified CiviCRM group.
	 *
	 * The query may be filtered in the following ways:
	 *
	 *     (1) by first letter ($mode = 'first_letter')
	 *     (2) by category ($mode = 'category')
	 *     (3) by name ($mode = 'name')
	 *     (4) by key word ($mode = 'keyword')
	 *
	 * @since 0.1.2
	 *
	 * @param array $group_id The numeric ID of the CiviCRM group.
	 * @param array $types The contact types to query.
	 * @param str $mode The mode in which to get contacts.
	 * @param str $field The field to query.
	 * @param str $query The filter query string.
	 * @return array $contacts The contacts in the CiviCRM group.
	 */
	public function contacts_get_for_group( $group_id, $types = array(), $mode = 'all', $field = '', $query = '' ) {

		// init return
		$contacts = array();

		// try and init CiviCRM
		if ( ! $this->initialize() ) return $contacts;

		// get contacts in group
		$params = array(
			'version' => 3,
			'group' => array( 'IN' => array( $group_id ) ),
			'options' => array(
				'limit' => '0', // no limit
			),
		);

		// amend params by type
		if ( ! empty( $types ) ) {

			// was a string passed?
			if ( ! is_array( $types ) AND is_string( $types ) ) {
				$types = array( $types );
			}

			// add param
			$params['contact_type'] = array( 'IN' => $types );

		}

		// amend params by mode
		switch( $mode ) {
			case 'first_letter' :
				$params[$field] = array( 'LIKE' => $query . '%' );
				break;
			case 'name' :
				$params[$field] = array( 'LIKE' => '%' . $query . '%' );
				break;
		}

		// get result
		$result = civicrm_api( 'contact', 'get', $params );

		// override return if we get some
		if (
			$result['is_error'] == 0 AND
			isset( $result['values'] ) AND
			count( $result['values'] ) > 0
		) {
			$contacts = $result['values'];
		}

		/*
		error_log( print_r( array(
			'method' => __METHOD__,
			'params' => $params,
			'contacts' => $contacts,
		), true ) );
		*/

		// --<
		return $contacts;

	}



	//##########################################################################



	/**
	 * Get top-level CiviCRM contact types.
	 *
	 * @since 0.1.2
	 *
	 * @return array $contact_types The top-level CiviCRM contact types.
	 */
	public function contact_types_get() {

		// init return
		$contact_types = array();

		// try and init CiviCRM
		if ( ! $this->initialize() ) return $contact_types;

		// define params to get all contact types
		$params = array(
			'version' => 3,
			'sequential' => 1,
			'is_active' => 1,
			'parent_id' => array( 'IS NULL' => 1 ),
			'options' => array(
				'limit' => '0', // no limit
			),
		);

		// get all contact_types
		$result = civicrm_api( 'ContactType', 'get', $params );

		// override return if we get some
		if (
			$result['is_error'] == 0 AND
			isset( $result['values'] ) AND
			count( $result['values'] ) > 0
		) {
			$contact_types = $result['values'];
		}

		/*
		error_log( print_r( array(
			'method' => __METHOD__,
			'params' => $params,
			'result' => $result,
		), true ) );
		*/

		// --<
		return $contact_types;

	}



	/**
	 * Get all CiviCRM contact types, nested by parent.
	 *
	 * CiviCRM only allows one level of nesting, so we can parse the results
	 * into a nested array to return.
	 *
	 * @since 0.1.2
	 *
	 * @return array $nested The nested CiviCRM contact types.
	 */
	public function contact_types_get_nested() {

		// init return
		$contact_types = array();

		// try and init CiviCRM
		if ( ! $this->initialize() ) return $contact_types;

		// define params to get all contact types
		$params = array(
			'version' => 3,
			'sequential' => 1,
			'is_active' => 1,
			'options' => array(
				'limit' => '0', // no limit
			),
		);

		// get all contact_types
		$result = civicrm_api( 'ContactType', 'get', $params );

		// override return if we get some
		if (
			$result['is_error'] == 0 AND
			isset( $result['values'] ) AND
			count( $result['values'] ) > 0
		) {
			$contact_types = $result['values'];
		}

		// let's get the top level types
		$top_level = array();
		foreach( $contact_types AS $contact_type ) {
			if ( ! isset( $contact_type['parent_id'] ) ) {
				$top_level[] = $contact_type;
			}
		}

		// make a nested array
		$nested = array();
		foreach( $top_level AS $item ) {
			$item['children'] = array();
			foreach( $contact_types AS $contact_type ) {
				if ( isset( $contact_type['parent_id'] ) AND $contact_type['parent_id'] == $item['id'] ) {
					$item['children'][] = $contact_type;
				}
			}
			$nested[] = $item;
		}

		/*
		error_log( print_r( array(
			'method' => __METHOD__,
			'params' => $params,
			'result' => $result,
			'nested' => $nested,
		), true ) );
		*/

		// --<
		return $nested;

	}



	//##########################################################################



	/**
	 * Get the standard fields for a CiviCRM Contact.
	 *
	 * @since 0.1.2
	 *
	 * @param array $types The field types of fields.
	 * @param str $filter Token by which to filter the array of fields.
	 * @return array $fields The array of fields.
	 */
	public function contact_fields_get( $types = array( 'Contact' ), $filter = 'none' ) {

		// init return
		$fields = array();

		// try and init CiviCRM
		if ( ! $this->initialize() ) return $fields;

		// construct params
		$params = array(
			'version' => 3,
			//'sequential' => 1,
			//'field_type' => array( 'IN' => $types ),
			/*
			'api.UFField.get' => array(
				'is_active' => 1,
				'options' => array(
					'limit' => '0', // no limit
				),
			),
			*/
			'options' => array(
				'limit' => '0', // no limit
			),
		);

		// hit the API
		$result = civicrm_api( 'Contact', 'getfields', $params );

		// override return if we get some
		if (
			$result['is_error'] == 0 AND
			isset( $result['values'] ) AND
			count( $result['values'] ) > 0
		) {

			// check for no filter
			if ( $filter == 'none' ) {

				// grab all of them
				$fields = $result['values'];

			// check public filter
			} elseif ( $filter == 'public' ) {

				// check against different field sets per type
				if ( in_array( 'Individual', $types ) ) {
					$contact_fields = $this->contact_fields_individual;
				}
				if ( in_array( 'Organization', $types ) ) {
					$contact_fields = $this->contact_fields_organization;
				}
				if ( in_array( 'Household', $types ) ) {
					$contact_fields = $this->contact_fields_household;
				}
				$contact_fields = array_merge( $contact_fields, $this->contact_fields_common );

				// skip all but those defined in our contact fields arrays
				foreach ( $result['values'] as $key => $value ) {
					if ( in_array( $value['name'], $contact_fields ) ) {
						$fields[] = $value;
					}
				}

			}

		}

		/*
		error_log( print_r( array(
			'method' => __METHOD__,
			'params' => $params,
			//'result' => $result,
			'fields' => $fields,
		), true ) );
		*/

		// --<
		return $fields;

	}



	/**
	 * Get the custom fields for a CiviCRM Contact.
	 *
	 * @since 0.1.2
	 *
	 * @param array $types The field types of the fields to retrieve.
	 * @return array $custom_fields The array of custom fields.
	 */
	public function contact_custom_fields_get( $types = array( 'Contact' ) ) {

		// init return
		$custom_fields = array();

		// try and init CiviCRM
		if ( ! $this->initialize() ) return $custom_fields;

		/**
		 * Allow filtering of the contact types.
		 *
		 * @since 0.1.2
		 *
		 * @param $types The existing contact types.
		 * @return $types The modified contact types.
		 */
		$types = apply_filters( 'civicrm_directory_contact_custom_fields_get_types', $types );

		// construct params
		$params = array(
			'version' => 3,
			'sequential' => 1,
			'is_active' => 1,
			'extends' => array( 'IN' => $types ),
			'api.CustomField.get' => array(
				'is_active' => 1,
				'options' => array(
					'limit' => '0', // no limit
				),
			),
			'options' => array(
				'limit' => '0', // no limit
			),
		);

		// hit the API
		$result = civicrm_api( 'CustomGroup', 'get', $params );

		// override return if we get some
		if (
			$result['is_error'] == 0 AND
			isset( $result['values'] ) AND
			count( $result['values'] ) > 0
		) {

			foreach( $result['values'] as $key => $value ) {
				foreach( $value['api.CustomField.get']['values'] as $key => $value ) {
					$custom_fields['custom_' . $value['id']] = $value['label'];
				}
			}

		}

		/*
		error_log( print_r( array(
			'method' => __METHOD__,
			'params' => $params,
			'result' => $result,
			'custom_fields' => $custom_fields,
		), true ) );
		*/

		// --<
		return $custom_fields;

	}



} // class ends



