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
	 * @param str $mode The mode in which to get contacts.
	 * @param str $query The field to query.
	 * @param str $query The filter query string.
	 * @return array $contacts The contacts in the CiviCRM group.
	 */
	public function contacts_get_for_group( $group_id, $mode = 'all', $field = '', $query = '' ) {

		// init return
		$contacts = array();

		// try and init CiviCRM
		if ( ! $this->initialize() ) return $groups;

		// get contacts in group
		$params = array(
			'version' => 3,
			'group' => array( 'IN' => array( $group_id ) ),
			'options' => array(
				'limit' => '0', // no limit
			),
		);

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



} // class ends



