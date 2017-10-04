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
			//'visibility' => 'Public Pages',
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

		/**
		 * Filter the params before hitting the API.
		 *
		 * @since 0.1.3
		 *
		 * @param array $params The existing CiviCRM API params.
		 * @param array $group_id The numeric ID of the CiviCRM group.
		 * @param array $types The contact types to query.
		 * @param str $mode The mode in which to get contacts.
		 * @param str $field The field to query.
		 * @param str $query The filter query string.
		 * @return array $params The modified CiviCRM API params.
		 */
		$params = apply_filters( 'civicrm_directory_contacts_get_for_group_params',
			$params, $group_id, $types, $mode, $field, $query
		);

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

		/**
		 * Filter the returned contacts.
		 *
		 * @since 0.1.3
		 *
		 * @param array $contacts The contacts retrieved from CiviCRM.
		 * @param array $group_id The numeric ID of the CiviCRM group.
		 * @param array $types The contact types to query.
		 * @param str $mode The mode in which to get contacts.
		 * @param str $field The field to query.
		 * @param str $query The filter query string.
		 * @return array $contacts The modified contacts retrieved from CiviCRM.
		 */
		$contacts = apply_filters( 'civicrm_directory_contacts_get_for_group',
			$contacts, $group_id, $types, $mode, $field, $query
		);

		// --<
		return $contacts;

	}



	//##########################################################################



	/**
	 * Get CiviCRM contact data by contact ID.
	 *
	 * @since 0.2.1
	 *
	 * @param int $contact_id The numeric ID of the CiviCRM contact.
	 * @param array $args Additional arguments to refine retrieval of a CiviCRM contact.
	 * @return mixed $civi_contact The array of data for the CiviCRM Contact, or false if not found
	 */
	public function contact_get_by_id( $contact_id, $args = array() ) {

		// try and init CiviCRM
		if ( ! $this->initialize() ) return false;

		// define params to get a contact
		$params = array(
			'version' => 3,
			'contact_id' => $contact_id,
		);

		// maybe add group
		if ( isset( $args['group_id'] ) ) {
			$params['group'] = array( 'IN' => array( $args['group_id'] ) );
		}

		// do we have any arguments?
		if ( ! empty( $args ) ) {

			// maybe construct returns query
			if ( isset( $args['returns'] ) ) {

				// construct returns array
				$returns = array();
				foreach( $args['returns'] AS $field_id ) {
					$returns[] = $field_id;
				}

				// add to params
				$params['return'] = $returns;

			}

			// maybe construct email query
			if ( isset( $args['api.Email.get'] ) ) {

				$email = array();
				foreach( $args['api.Email.get'] AS $field_id ) {
					$email[] = $field_id;
				}

				// add to params if we have some types
				if ( ! empty( $email ) ) {
					$params['api.Email.get'] = array(
						'location_type_id' => array( 'IN' => $email ),
						'return' => array( 'location_type_id', 'email' ),
					);
				}

			}

			// maybe construct website query
			if ( isset( $args['api.Website.get'] ) ) {

				$website = array();
				foreach( $args['api.Website.get'] AS $field_id ) {
					$website[] = $field_id;
				}

				// add to params if we have some types
				if ( ! empty( $website ) ) {
					$params['api.Website.get'] = array(
						'website_type_id' => array( 'IN' => $website ),
						'return' => array( 'website_type_id', 'url' ),
					);
				}

			}

			// maybe construct phone query
			if ( isset( $args['api.Phone.get'] ) ) {

				$location_types = array();
				foreach( $args['api.Phone.get'] AS $loc_type_id => $fields ) {
					$location_types[] = $loc_type_id;
				}

				$location_fields = array( 'location_type_id' );
				foreach( $args['api.Phone.get'] AS $loc_type_id => $fields ) {
					foreach( $fields AS $field ) {
						$location_fields[] = $field;
					}
				}

				// add to params if we have some types
				if ( ! empty( $location_types ) AND ! empty( $location_fields ) ) {
					$params['api.Phone.get'] = array(
						'location_type_id' => array( 'IN' => $location_types ),
						'return' => array_unique( $location_fields ),
					);
				}

			}

			// maybe construct address query
			if ( isset( $args['api.Address.get'] ) ) {

				$location_types = array();
				foreach( $args['api.Address.get'] AS $loc_type_id => $fields ) {
					$location_types[] = $loc_type_id;
				}

				$location_fields = array( 'location_type_id' );
				foreach( $args['api.Address.get'] AS $loc_type_id => $fields ) {
					foreach( $fields AS $field ) {
						$location_fields[] = $field;
						if ( $field == 'country_id' ) {
							$location_fields[] = 'country_id.name';
						}
						if ( $field == 'state_province_id' ) {
							$location_fields[] = 'state_province_id.name';
						}
					}
				}

				// add to params if we have some types
				if ( ! empty( $location_types ) AND ! empty( $location_fields ) ) {
					$params['api.Address.get'] = array(
						'location_type_id' => array( 'IN' => $location_types ),
						'return' => array_unique( $location_fields ),
					);
				}

			}

		}

		// use API
		$contact_data = civicrm_api( 'contact', 'get', $params );

		// bail if we get any errors
		if ( $contact_data['is_error'] == 1 ) return false;
		if ( ! isset( $contact_data['values'] ) ) return false;
		if ( count( $contact_data['values'] ) === 0 ) return false;

		// get contact
		$contact = array_shift( $contact_data['values'] );

		// --<
		return $contact;

	}



	/**
	 * Get top-level CiviCRM contact types.
	 *
	 * @since 0.1.2
	 *
	 * @return array $contact_types The top-level CiviCRM contact types.
	 */
	public function contact_types_get_all() {

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

			// we only need the results from the chained API data
			foreach( $result['values'] as $key => $value ) {
				foreach( $value['api.CustomField.get']['values'] as $key => $value ) {
					$custom_fields[$key] = $value;
				}
			}

		}

		// --<
		return $custom_fields;

	}



	/**
	 * Get the types of email.
	 *
	 * @since 0.2.2
	 *
	 * @return array $email_types The array of email types.
	 */
	public function email_types_get() {

		// init return
		$email_types = array();

		// construct params to get all email types
		$params = array(
			'version' => 3,
			'sequential' => 1,
			'field' => 'location_type_id',
		);

		// hit the API
		$result = civicrm_api( 'Email', 'getoptions', $params );

		// override return if we get some
		if (
			$result['is_error'] == 0 AND
			isset( $result['values'] ) AND
			count( $result['values'] ) > 0
		) {
			$email_types = $result['values'];
		}

		// --<
		return $email_types;

	}



	/**
	 * Get the types of website.
	 *
	 * @since 0.2.2
	 *
	 * @return array $website_types The array of website types.
	 */
	public function website_types_get() {

		// init return
		$website_types = array();

		// construct params to get all website types
		$params = array(
			'version' => 3,
			'sequential' => 1,
			'field' => 'website_type_id',
		);

		// hit the API
		$result = civicrm_api( 'Website', 'getoptions', $params );

		// override return if we get some
		if (
			$result['is_error'] == 0 AND
			isset( $result['values'] ) AND
			count( $result['values'] ) > 0
		) {
			$website_types = $result['values'];
		}

		// --<
		return $website_types;

	}



	/**
	 * Get the types of phone.
	 *
	 * @since 0.2.2
	 *
	 * @return array $phone_types The array of phone types.
	 */
	public function phone_types_get() {

		// init return
		$phone_types = array();

		// construct params to get all phone types
		$params = array(
			'version' => 3,
			'sequential' => 1,
			'field' => 'phone_type_id',
		);

		// hit the API
		$result = civicrm_api( 'Phone', 'getoptions', $params );

		// override return if we get some
		if (
			$result['is_error'] == 0 AND
			isset( $result['values'] ) AND
			count( $result['values'] ) > 0
		) {
			$phone_types = $result['values'];
		}

		// --<
		return $phone_types;

	}



	/**
	 * Get the types of address.
	 *
	 * @since 0.2.2
	 *
	 * @return array $address_types The array of address types.
	 */
	public function address_types_get() {

		// init return
		$address_types = array();

		// construct params to get all address types
		$params = array(
			'version' => 3,
			'sequential' => 1,
			'field' => 'location_type_id',
		);

		// hit the API
		$result = civicrm_api( 'Address', 'getoptions', $params );

		// override return if we get some
		if (
			$result['is_error'] == 0 AND
			isset( $result['values'] ) AND
			count( $result['values'] ) > 0
		) {
			$address_types = $result['values'];
		}

		// --<
		return $address_types;

	}



	/**
	 * Get the data for the address fields.
	 *
	 * @since 0.2.2
	 *
	 * @return array $address_fields The array of address fields.
	 */
	public function address_fields_get() {

		// init return
		$address_fields = array();

		// construct params to get all address fields
		$params = array(
			'version' => 3,
			'sequential' => 1,
		);

		// hit the API
		$result = civicrm_api( 'Address', 'getfields', $params );

		// override return if we get some
		if (
			$result['is_error'] == 0 AND
			isset( $result['values'] ) AND
			count( $result['values'] ) > 0
		) {
			$address_fields = $result['values'];
		}

		// --<
		return $address_fields;

	}



} // class ends



