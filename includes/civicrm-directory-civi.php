<?php
/**
 * CiviCRM Class.
 *
 * Handles functionality that interacts with CiviCRM.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CiviCRM Class.
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
	public $contact_fields_common = [
		'nick_name',
		'email',
		'source',
	];

	/**
	 * Contact fields to make available on front end.
	 *
	 * @since 0.1.2
	 * @access public
	 * @var array $contact_fields_individual The public contact fields for Individuals.
	 */
	public $contact_fields_individual = [
		'prefix_id',
		'first_name',
		'last_name',
		'middle_name',
		'suffix_id',
		'job_title',
		'gender_id',
		'birth_date',
		'current_employer',
	];

	/**
	 * Contact fields for Organisations to make available on front end.
	 *
	 * @since 0.1.2
	 * @access public
	 * @var array $contact_fields_organization The public contact fields for Organisations.
	 */
	public $contact_fields_organization = [
		'legal_name',
		'organization_name',
		'sic_code',
	];

	/**
	 * Contact fields for Households to make available on front end.
	 *
	 * @since 0.1.2
	 * @access public
	 * @var array $contact_fields_household The public contact fields for Households.
	 */
	public $contact_fields_household = [
		'household_name',
	];

	/**
	 * Constructor.
	 *
	 * @since 0.1.2
	 *
	 * @param object $parent The parent object.
	 */
	public function __construct( $parent ) {

		// Store plugin reference.
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

		// Try and init CiviCRM.
		if ( ! function_exists( 'civi_wp' ) ) {
			return false;
		}

		if ( ! civi_wp()->initialize() ) {
			return false;
		}

		// --<
		return true;

	}

	//##########################################################################

	/**
	 * Get all CiviCRM groups.
	 *
	 * @since 0.1.2
	 *
	 * @return array $groups The array of all CiviCRM groups.
	 */
	public function groups_get() {

		// Init return.
		$groups = [];

		// Try and init CiviCRM.
		if ( ! $this->initialize() ) {
			return $groups;
		}

		// Define params to get all groups.
		$params = [
			'version' => 3,
			'is_active' => 1,
			'is_hidden' => 0,
			'options' => [
				'limit' => '0', // No limit.
			],
		];

		// Get all groups.
		$all_groups = civicrm_api( 'group', 'get', $params );

		// Override return if we get some.
		if (
			$all_groups['is_error'] == 0 &&
			isset( $all_groups['values'] ) &&
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
	public function contacts_get_for_group( $group_id, $types = [], $mode = 'all', $field = '', $query = '' ) {

		// Init return.
		$contacts = [];

		// Try and init CiviCRM.
		if ( ! $this->initialize() ) {
			return $contacts;
		}

		// Get contacts in group.
		$params = [
			'version' => 3,
			'group' => [
				'IN' => [ $group_id ],
			],
			'options' => [
				'limit' => '0', // No limit.
			],
		];

		// Amend params by type.
		if ( ! empty( $types ) ) {

			// Was a string passed?
			if ( ! is_array( $types ) && is_string( $types ) ) {
				$types = [ $types ];
			}

			// Add param.
			$params['contact_type'] = [ 'IN' => $types ];

		}

		// Amend params by mode.
		switch ( $mode ) {
			case 'first_letter':
				$params[ $field ] = [ 'LIKE' => $query . '%' ];
				break;
			case 'name':
				$params[ $field ] = [ 'LIKE' => '%' . $query . '%' ];
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

		// Get result.
		$result = civicrm_api( 'contact', 'get', $params );

		// Override return if we get some.
		if (
			$result['is_error'] == 0 &&
			isset( $result['values'] ) &&
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
	 * @return mixed $civi_contact The array of data for the CiviCRM Contact, or false if not found.
	 */
	public function contact_get_by_id( $contact_id, $args = [] ) {

		// Try and init CiviCRM.
		if ( ! $this->initialize() ) {
			return false;
		}

		// Define params to get a contact.
		$params = [
			'version' => 3,
			'contact_id' => $contact_id,
		];

		// Maybe add group.
		if ( isset( $args['group_id'] ) ) {
			$params['group'] = [ 'IN' => [ $args['group_id'] ] ];
		}

		// Do we have any arguments?
		if ( ! empty( $args ) ) {

			// Maybe construct returns query.
			if ( isset( $args['returns'] ) ) {

				// Construct returns array.
				$returns = [];
				foreach ( $args['returns'] as $field_id ) {
					$returns[] = $field_id;
				}

				// Add to params.
				$params['return'] = $returns;

			}

			// Maybe construct email query.
			if ( isset( $args['api.Email.get'] ) ) {

				$email = [];
				foreach ( $args['api.Email.get'] as $field_id ) {
					$email[] = $field_id;
				}

				// Add to params if we have some types.
				if ( ! empty( $email ) ) {
					$params['api.Email.get'] = [
						'location_type_id' => [ 'IN' => $email ],
						'return' => [ 'location_type_id', 'email' ],
					];
				}

			}

			// Maybe construct website query.
			if ( isset( $args['api.Website.get'] ) ) {

				$website = [];
				foreach ( $args['api.Website.get'] as $field_id ) {
					$website[] = $field_id;
				}

				// Add to params if we have some types.
				if ( ! empty( $website ) ) {
					$params['api.Website.get'] = [
						'website_type_id' => [ 'IN' => $website ],
						'return' => [ 'website_type_id', 'url' ],
					];
				}

			}

			// Maybe construct phone query.
			if ( isset( $args['api.Phone.get'] ) ) {

				$location_types = [];
				foreach ( $args['api.Phone.get'] as $loc_type_id => $fields ) {
					$location_types[] = $loc_type_id;
				}

				$location_fields = [ 'location_type_id' ];
				foreach ( $args['api.Phone.get'] as $loc_type_id => $fields ) {
					foreach ( $fields as $field ) {
						$location_fields[] = $field;
					}
				}

				// Add to params if we have some types.
				if ( ! empty( $location_types ) && ! empty( $location_fields ) ) {
					$params['api.Phone.get'] = [
						'location_type_id' => [ 'IN' => $location_types ],
						'return' => array_unique( $location_fields ),
					];
				}

			}

			// Maybe construct address query.
			if ( isset( $args['api.Address.get'] ) ) {

				$location_types = [];
				foreach ( $args['api.Address.get'] as $loc_type_id => $fields ) {
					$location_types[] = $loc_type_id;
				}

				$location_fields = [ 'location_type_id' ];
				foreach ( $args['api.Address.get'] as $loc_type_id => $fields ) {
					foreach ( $fields as $field ) {
						$location_fields[] = $field;
						if ( $field == 'country_id' ) {
							$location_fields[] = 'country_id.name';
						}
						if ( $field == 'state_province_id' ) {
							$location_fields[] = 'state_province_id.name';
						}
					}
				}

				// Add to params if we have some types.
				if ( ! empty( $location_types ) && ! empty( $location_fields ) ) {
					$params['api.Address.get'] = [
						'location_type_id' => [ 'IN' => $location_types ],
						'return' => array_unique( $location_fields ),
					];
				}

			}

		}

		// Use API.
		$contact_data = civicrm_api( 'contact', 'get', $params );

		// Bail if we get any errors.
		if ( $contact_data['is_error'] == 1 ) {
			return false;
		}
		if ( ! isset( $contact_data['values'] ) ) {
			return false;
		}
		if ( count( $contact_data['values'] ) === 0 ) {
			return false;
		}

		// Get contact.
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

		// Init return.
		$contact_types = [];

		// Try and init CiviCRM.
		if ( ! $this->initialize() ) {
			return $contact_types;
		}

		// Define params to get all contact types.
		$params = [
			'version' => 3,
			'sequential' => 1,
			'is_active' => 1,
			'parent_id' => [ 'IS NULL' => 1 ],
			'options' => [
				'limit' => '0', // No limit.
			],
		];

		// Get all contact_types.
		$result = civicrm_api( 'ContactType', 'get', $params );

		// Override return if we get some.
		if (
			$result['is_error'] == 0 &&
			isset( $result['values'] ) &&
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

		// Init return.
		$contact_types = [];

		// Try and init CiviCRM.
		if ( ! $this->initialize() ) {
			return $contact_types;
		}

		// Define params to get all Contact Types.
		$params = [
			'version' => 3,
			'sequential' => 1,
			'is_active' => 1,
			'options' => [
				'limit' => '0', // No limit.
			],
		];

		// Get all Contact Types.
		$result = civicrm_api( 'ContactType', 'get', $params );

		// Override return if we get some.
		if (
			$result['is_error'] == 0 &&
			isset( $result['values'] ) &&
			count( $result['values'] ) > 0
		) {
			$contact_types = $result['values'];
		}

		// Let's get the top level types.
		$top_level = [];
		foreach ( $contact_types as $contact_type ) {
			if ( ! isset( $contact_type['parent_id'] ) ) {
				$top_level[] = $contact_type;
			}
		}

		// Make a nested array.
		$nested = [];
		foreach ( $top_level as $item ) {
			$item['children'] = [];
			foreach ( $contact_types as $contact_type ) {
				if ( isset( $contact_type['parent_id'] ) && $contact_type['parent_id'] == $item['id'] ) {
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
	public function contact_fields_get( $types = [ 'Contact' ], $filter = 'none' ) {

		// Init return.
		$fields = [];

		// Try and init CiviCRM.
		if ( ! $this->initialize() ) {
			return $fields;
		}

		// Construct params.
		$params = [
			'version' => 3,
			'options' => [
				'limit' => '0', // No limit.
			],
		];

		// Hit the API.
		$result = civicrm_api( 'Contact', 'getfields', $params );

		// Override return if we get some.
		if (
			$result['is_error'] == 0 &&
			isset( $result['values'] ) &&
			count( $result['values'] ) > 0
		) {

			// Check for no filter.
			if ( $filter == 'none' ) {

				// Grab all of them.
				$fields = $result['values'];

			// Check public filter.
			} elseif ( $filter == 'public' ) {

				// Check against different field sets per type.
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

				// Skip all but those defined in our contact fields arrays.
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
	public function contact_custom_fields_get( $types = [ 'Contact' ] ) {

		// Init return.
		$custom_fields = [];

		// Try and init CiviCRM.
		if ( ! $this->initialize() ) {
			return $custom_fields;
		}

		/**
		 * Allow filtering of the contact types.
		 *
		 * @since 0.1.2
		 *
		 * @param $types The existing contact types.
		 * @return $types The modified contact types.
		 */
		$types = apply_filters( 'civicrm_directory_contact_custom_fields_get_types', $types );

		// Construct params.
		$params = [
			'version' => 3,
			'sequential' => 1,
			'is_active' => 1,
			'extends' => [ 'IN' => $types ],
			'api.CustomField.get' => [
				'is_active' => 1,
				'options' => [
					'limit' => '0', // No limit.
				],
			],
			'options' => [
				'limit' => '0', // No limit.
			],
		];

		// Hit the API.
		$result = civicrm_api( 'CustomGroup', 'get', $params );

		// Override return if we get some.
		if (
			$result['is_error'] == 0 &&
			isset( $result['values'] ) &&
			count( $result['values'] ) > 0
		) {

			// We only need the results from the chained API data.
			foreach ( $result['values'] as $key => $value ) {
				foreach ( $value['api.CustomField.get']['values'] as $key => $value ) {
					$custom_fields[ $key ] = $value;
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

		// Init return.
		$email_types = [];

		// Construct params to get all email types.
		$params = [
			'version' => 3,
			'sequential' => 1,
			'field' => 'location_type_id',
		];

		// Hit the API.
		$result = civicrm_api( 'Email', 'getoptions', $params );

		// Override return if we get some.
		if (
			$result['is_error'] == 0 &&
			isset( $result['values'] ) &&
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

		// Init return.
		$website_types = [];

		// Construct params to get all website types.
		$params = [
			'version' => 3,
			'sequential' => 1,
			'field' => 'website_type_id',
		];

		// Hit the API.
		$result = civicrm_api( 'Website', 'getoptions', $params );

		// Override return if we get some.
		if (
			$result['is_error'] == 0 &&
			isset( $result['values'] ) &&
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

		// Init return.
		$phone_types = [];

		// Construct params to get all phone types.
		$params = [
			'version' => 3,
			'sequential' => 1,
			'field' => 'phone_type_id',
		];

		// Hit the API.
		$result = civicrm_api( 'Phone', 'getoptions', $params );

		// Override return if we get some.
		if (
			$result['is_error'] == 0 &&
			isset( $result['values'] ) &&
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

		// Init return.
		$address_types = [];

		// Construct params to get all address types.
		$params = [
			'version' => 3,
			'sequential' => 1,
			'field' => 'location_type_id',
		];

		// Hit the API.
		$result = civicrm_api( 'Address', 'getoptions', $params );

		// Override return if we get some.
		if (
			$result['is_error'] == 0 &&
			isset( $result['values'] ) &&
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

		// Init return.
		$address_fields = [];

		// Construct params to get all address fields.
		$params = [
			'version' => 3,
			'sequential' => 1,
		];

		// Hit the API.
		$result = civicrm_api( 'Address', 'getfields', $params );

		// Override return if we get some.
		if (
			$result['is_error'] == 0 &&
			isset( $result['values'] ) &&
			count( $result['values'] ) > 0
		) {
			$address_fields = $result['values'];
		}

		// --<
		return $address_fields;

	}

}
