<?php

/**
 * CiviCRM Directory Template Class.
 *
 * A class that encapsulates templating functionality for CiviCRM Directory.
 *
 * @package CiviCRM_Directory
 */
class CiviCRM_Directory_Template {

	/**
	 * Plugin (calling) object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $plugin The plugin object.
	 */
	public $plugin;

	/**
	 * Viewed contact.
	 *
	 * @since 0.2.1
	 * @access public
	 * @var array $contact The requested contact data.
	 */
	public $contact = false;


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

		// override some page elements
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 5 );

		// filter the content
		add_filter( 'the_content', array( $this, 'directory_render' ) );

	}



	/**
	 * Actions to perform on plugin activation.
	 *
	 * @since 0.1
	 */
	public function activate() {

	}



	/**
	 * Actions to perform on plugin deactivation (NOT deletion).
	 *
	 * @since 0.1
	 */
	public function deactivate() {

	}



	// #########################################################################



	/**
	 * Check query for directory entry view request.
	 *
	 * @since 0.2.1
	 */
	public function pre_get_posts( $query ) {

		// front end and main query?
		if ( ! is_admin() AND $query->is_main_query() ) {

			// are we viewing a contact?
			$contact_id = $query->get( 'entry' );
			if ( ! empty( $contact_id ) ) {

				// sanity check
				$contact_id = absint( $contact_id );

				// reject failed conversions
				if ( $contact_id == 0 ) return $query;

				// set up contact once query is complete
				add_action( 'wp', array( $this, 'entry_setup' ) );

				// store contact ID for retrieval in entry_setup()
				$this->contact_id = $contact_id;

			}

		}

	}



	/**
	 * Set up data and hooks for directory contact view.
	 *
	 * @since 0.2.4
	 */
	public function entry_setup() {

		// bail if we don't have a valid countact ID
		if ( ! isset( $this->contact_id ) ) return;
		if ( ! is_int( $this->contact_id ) ) return;

		// loop through the results and get group ID from post meta
		if ( have_posts() ) {
			while ( have_posts() ) : the_post();
				global $post;
				$group_id = $this->plugin->metaboxes->group_id_get( $post->ID );
			endwhile;
		}

		// reset loop
		rewind_posts();

		// sanity check
		if ( empty( $group_id ) ) return $results;

		// restrict to group
		$args = array(
			'group_id' => $group_id,
		);

		// get contact
		$this->contact = $this->plugin->civi->contact_get_by_id( $this->contact_id, $args );

		// filter the document title for themes that still use wp_title()
		add_filter( 'wp_title', array( $this, 'document_title' ), 20, 3 );

		// filter the document title for themes that support 'title-tag'
		add_filter( 'document_title_parts', array( $this, 'document_title_parts' ), 20 );

		// filter the title
		add_filter( 'the_title', array( $this, 'the_title' ), 10 );

		// override the initial map query
		add_filter( 'civicrm_directory_map_contacts', array( $this, 'map_query_filter' ) );

	}



	/**
	 * Override document title when viewing a contact in the directory.
	 *
	 * This filter only kicks in with themes that do not add theme support for
	 * 'title-tag' and still use wp_title(). This includes default themes up to
	 * Twenty Fourteen, so it's worth supporting.
	 *
	 * @since 0.2.4
	 *
	 * @param string $title The page title.
	 * @param string $sep Title separator.
	 * @param string $seplocation Location of the separator (left or right).
	 * @return string $title The modified page title.
	 */
	public function document_title( $title, $sep, $sep_location ) {

		// safety first
		if ( ! isset( $this->contact['display_name'] ) ) return $title;

		// sep on right, so reverse the order
		if ( 'right' == $sep_location ) {
			$title = $this->contact['display_name'] . " $sep " . $title;
		} else {
			$title = $title . " $sep " . $this->contact['display_name'];
		}

		// --<
		return $title;

	}



	/**
	 * Add the root network name when the sub-blog is a group blog.
	 *
	 * @since 3.8
	 *
	 * @param array $parts The existing title parts
	 * @return array $parts The modified title parts
	 */
	public function document_title_parts( $parts ) {

		// safety first
		if ( ! isset( $this->contact['display_name'] ) ) return $parts;

		// prepend the contact display name
		$parts = array( 'name' => $this->contact['display_name'] ) + $parts;

		// --<
		return $parts;

	}



	/**
	 * Override title of the directory page when viewing a contact.
	 *
	 * @since 0.2.1
	 *
	 * @param string $title The existing title.
	 * @return string $title The modified title.
	 */
	public function the_title( $title ) {

		global $wp_query;

		// are we viewing a contact?
		if (
			! empty( $wp_query->query_vars['entry'] ) AND
			is_singular( 'directory' ) AND
			in_the_loop()
		) {

			// override title if we're successful
			if ( $this->contact !== false ) {
				$title = $this->contact['display_name'];
			}

		}

		// --<
		return $title;

	}



	/**
	 * Get the title of the directory page.
	 *
	 * @since 0.2.2
	 *
	 * @param int $post_id The numeric ID of the directory.
	 * @return str $title The directory title.
	 */
	public function get_the_title( $post_id = null ) {

		// use current post if none passed
		if ( is_null( $post_id ) ) $post_id = get_the_ID();

		// remove filter
		remove_filter( 'the_title', array( $this, 'the_title' ), 10 );

		// get title
		$title = get_the_title( $post_id );

		// re-add filter
		add_filter( 'the_title', array( $this, 'the_title' ), 10 );

		// --<
		return $title;

	}



	/**
	 * Construct the markup for a contact.
	 *
	 * @since 0.2.2
	 *
	 * @return string $markup The constructed markup for a contact.
	 */
	public function the_contact() {

		// init return
		$markup = '';

		// bail if we don't have a contact
		if ( $this->contact === false ) return $markup;

		// grab contact type
		$contact_type = $this->contact['contact_type'];

		// init contact types
		$types = array( 'Contact', $contact_type );

		// get all public contact fields
		$all_contact_fields = $this->plugin->civi->contact_fields_get( $types, 'public' );

		// build reference array
		$core_refs = array();
		foreach( $all_contact_fields AS $contact_field ) {
			$core_refs[$contact_field['name']] = $contact_field['title'];
		}

		// get contact custom fields
		$all_contact_custom_fields = $this->plugin->civi->contact_custom_fields_get( $types );

		// extract just the reference data
		$custom_field_refs = array();
		foreach( $all_contact_custom_fields as $key => $value ) {
			$custom_field_refs[$value['id']] = $value['label'];
		}

		// fields that have associated option groups
		$fields_with_optgroups = array( 'Select', 'Radio', 'CheckBox', 'Multi-Select', 'AdvMulti-Select' );

		// fill out the content of the custom fields
		$custom_option_refs = array();
		foreach( $all_contact_custom_fields AS $key => $field ) {

			// if this field type doesn't have an option group
			if ( ! in_array( $field['html_type'], $fields_with_optgroups ) ) {

				// grab data format
				$custom_option_refs[$field['id']] = $field['data_type'];

			} else {

				// grab data from option group
				if ( isset( $field['option_group_id'] ) AND ! empty( $field['option_group_id'] ) ) {
					$custom_option_refs[$field['id']] = CRM_Core_OptionGroup::valuesByID( absint( $field['option_group_id'] ) );
				}

			}

		}

		// build reference array
		$other_refs = array();

		// get all email types
		$email_types = $this->plugin->civi->email_types_get();

		// build reference array
		foreach( $email_types AS $email_type ) {
			$other_refs['email'][$email_type['key']] = $email_type['value'];
		}

		// get all website types
		$website_types = $this->plugin->civi->website_types_get();

		// build reference array
		foreach( $website_types AS $website_type ) {
			$other_refs['website'][$website_type['key']] = $website_type['value'];
		}

		// get all phone types
		$phone_types = $this->plugin->civi->phone_types_get();

		// build reference array
		foreach( $phone_types AS $phone_type ) {
			$other_refs['phone'][$phone_type['key']] = $phone_type['value'];
		}

		// get all address types
		$address_types = $this->plugin->civi->address_types_get();

		// build reference array
		foreach( $address_types AS $address_type ) {
			$other_refs['address']['locations'][$address_type['key']] = $address_type['value'];
		}

		// get all address fields
		$address_fields = $this->plugin->civi->address_fields_get();

		// build reference array
		foreach( $address_fields AS $address_field ) {
			$other_refs['address']['fields'][$address_field['name']] = $address_field['title'];
		}

		// get contact fields data
		$contact_fields = $this->plugin->metaboxes->contact_fields_get();

		// init args
		$args = array(
			'returns' => array(),
			'api.Email.get' => array(),
			'api.Website.get' => array(),
			'api.Phone.get' => array(),
			'api.Address.get' => array(),
		);

		// build fields-to-return
		$fields_core = isset( $contact_fields[$contact_type]['core'] ) ? $contact_fields[$contact_type]['core'] : array();
		foreach( $fields_core AS $key => $field ) {
			$args['returns'][] = $field;
		}
		$fields_custom = isset( $contact_fields[$contact_type]['custom'] ) ? $contact_fields[$contact_type]['custom'] : array();
		foreach( $fields_custom AS $field ) {
			$args['returns'][] = 'custom_' . $field;
		}

		// build chained API calls
		$fields_other = isset( $contact_fields[$contact_type]['other'] ) ? $contact_fields[$contact_type]['other'] : array();
		foreach( $fields_other AS $field ) {

			if ( $field == 'email' ) {
				foreach( $contact_fields[$contact_type]['email']['enabled'] AS $email ) {
					$args['api.Email.get'][] = $email;
				}
			}

			if ( $field == 'website' ) {
				foreach( $contact_fields[$contact_type]['website']['enabled'] AS $website ) {
					$args['api.Website.get'][] = $website;
				}
			}

			if ( $field == 'phone' ) {
				foreach( $contact_fields[$contact_type]['phone']['enabled'] AS $loc_type ) {
					$args['api.Phone.get'][$loc_type] = $contact_fields[$contact_type]['phone'][$loc_type];
				}
			}

			if ( $field == 'address' ) {
				foreach( $contact_fields[$contact_type]['address']['enabled'] AS $loc_type ) {
					$args['api.Address.get'][$loc_type] = $contact_fields[$contact_type]['address'][$loc_type];
				}
			}

		}

		// restrict to group
		$args['group_id'] = $this->plugin->metaboxes->group_id_get( get_the_ID() );

		// get contact again, this time with custom fields etc
		$contact_data = $this->plugin->civi->contact_get_by_id( $this->contact['contact_id'], $args );

		// init template var
		$contact = array();

		// build core data array
		foreach( $fields_core AS $field ) {
			if ( ! empty( $contact_data[$field] ) ) {
				$contact['core'][] = array(
					'label' => $core_refs[$field],
					'value' => $contact_data[$field],
				);
			}
		}

		// build custom data array
		foreach( $fields_custom AS $field_id ) {
			if ( ! empty( $contact_data['custom_' . $field_id] ) ) {
				if ( is_array( $custom_option_refs[$field_id] ) ) {
					$value = $custom_option_refs[$field_id][$contact_data['custom_' . $field_id]];
				} else {
					$value = $contact_data['custom_' . $field_id];
				}
				$contact['custom'][] = array(
					'label' => $custom_field_refs[$field_id],
					'value' => $value,
				);
			}
		}

		// build other data arrays
		foreach( $fields_other AS $field ) {

			if ( $field == 'email' ) {
				foreach( $contact_data['api.Email.get']['values'] AS $item ) {
					if ( ! empty( $item['email'] ) ) {
						$contact[$field][$item['location_type_id']] = array(
							'label' => $other_refs[$field][$item['location_type_id']],
							'value' => $item['email'],
						);
					}
				}
			}

			if ( $field == 'website' ) {
				foreach( $contact_data['api.Website.get']['values'] AS $item ) {
					if ( ! empty( $item['url'] ) ) {
						$contact[$field][$item['website_type_id']] = array(
							'label' => $other_refs[$field][$item['website_type_id']],
							'value' => $item['url'],
						);
					}
				}
			}

			if ( $field == 'phone' ) {
				foreach( $contact_data['api.Phone.get']['values'] AS $item ) {
					if ( ! empty( $item['phone'] ) ) {
						$contact[$field][$item['location_type_id']] = array(
							'label' => $other_refs['email'][$item['location_type_id']],
							'value' => $other_refs[$field][$item['phone_type_id']] . ': ' . $item['phone'],
						);
					}
				}
			}

			if ( $field == 'address' ) {

				// init data for this address
				foreach( $contact_fields[$contact_type][$field]['enabled'] AS $location_type_id ) {

					$fields = $contact_fields[$contact_type][$field][$location_type_id];

					$contact[$field][$location_type_id] = array(
						'label' => $other_refs[$field]['locations'][$location_type_id],
						'address' => array(),
					);

					foreach( $contact_data['api.Address.get']['values'] AS $item ) {

						foreach( $item AS $key => $value ) {

							// skip nested queries
							if ( $key == 'state_province_id.name' ) continue;
							if ( $key == 'country_id.name' ) continue;

							// skip if not asked for
							if ( ! in_array( $key, $contact_fields[$contact_type][$field][$location_type_id] ) ) continue;
							if ( $location_type_id != $item['location_type_id'] ) continue;

							// skip if empty
							if ( empty( $value ) ) continue;

							// init label
							$label = $other_refs[$field]['fields'][$key];

							// handle some fields differently
							if ( $key == 'state_province_id' ) {
								$value = $item['state_province_id.name'];
								$label = __( 'State/Province', 'civicrm-directory' );
							}
							if ( $key == 'country_id' ) {
								$value = $item['country_id.name'];
								$label = __( 'Country', 'civicrm-directory' );
							}

							// add to data array
							$contact[$field][$location_type_id]['address'][] = array(
								'label' => $label,
								'value' => $value,
							);

						}

					}

					// unset if empty
					if ( empty( $contact[$field][$location_type_id]['address'] ) ) {
						unset( $contact[$field][$location_type_id] );
					}

				}

			}

		}

		// use template
		$file = 'civicrm-directory/directory-details.php';

		// get template
		$template = $this->find_file( $file );

		// buffer the template part
		ob_start();
		include( $template );
		$markup = ob_get_contents();
		ob_end_clean();

		// --<
		return $markup;

	}



	/**
	 * Override the initial map query.
	 *
	 * @since 0.2.1
	 *
	 * @param array $contacts The contacts retrieved from CiviCRM.
	 * @return array $contacts The modified contacts retrieved from CiviCRM.
	 */
	public function map_query_filter( $contacts ) {

		// override if viewing a contact
		if ( $this->contact !== false ) {
			$contacts = array( $this->contact );
		}

		// --<
		return $contacts;

	}



	/**
	 * Callback filter to display a Directory.
	 *
	 * @param str $content The existing content.
	 * @return str $content The modified content.
	 */
	function directory_render( $content ) {

		global $wp_query;

		// only on canonical Directory pages
		if ( ! is_singular( $this->plugin->cpt->post_type_name ) ) {
			return $content;
		}

		// only for our post type
		if ( get_post_type( get_the_ID() ) !== $this->plugin->cpt->post_type_name ) {
			return $content;
		}

		// are we viewing a contact?
		if ( ! empty( $this->contact ) ) {
			$file = 'civicrm-directory/directory-contact.php';
		} else {
			$file = 'civicrm-directory/directory-index.php';
		}

		// get template
		$template = $this->find_file( $file );

		// buffer the template part
		ob_start();
		include( $template );
		$content = ob_get_contents();
		ob_end_clean();

		// --<
		return $content;

	}



	/**
	 * Insert the listing markup.
	 *
	 * @since 0.1
	 *
	 * @param array $data The configuration data.
	 */
	public function insert_markup( $data = array() ) {

		/**
		 * Data can be amended (or created) by callbacks for this filter.
		 *
		 * @since 0.1.3
		 *
		 * @param array $data The existing template data.
		 * @return array $data The modified template data.
		 */
		$data = apply_filters( 'civicrm_directory_listing_markup', $data );

		// init template vars
		$listing = isset( $data['listing'] ) ? $data['listing'] : '';
		$feedback = isset( $data['feedback'] ) ? $data['feedback'] : '';

		// get template
		$template = $this->find_file( 'civicrm-directory/directory-listing.php' );

		// include the template part
		include( $template );

	}



	/**
	 * Find a template given a relative path.
	 *
	 * Example: 'civicrm-directory/directory-search.php'
	 *
	 * @since 0.1
	 *
	 * @param str $template_path The relative path to the template.
	 * @return str|bool $full_path The absolute path to the template, or false on failure.
	 */
	function find_file( $template_path ) {

		// get stack
		$stack = $this->template_stack();

		// constuct templates array
		$templates = array();
		foreach( $stack As $location ) {
			$templates[] = trailingslashit( $location ) . $template_path;
		}

		// let's look for it
		$full_path = false;
		foreach ( $templates AS $template ) {
			if ( file_exists( $template ) ) {
				$full_path = $template;
				break;
			}
		}

		// --<
		return $full_path;

	}



	/**
	 * Construct template stack.
	 *
	 * @since 0.1
	 *
	 * @return array $stack The stack of locations to look for a template in.
	 */
	function template_stack() {

		// define paths
		$template_dir = get_stylesheet_directory();
		$parent_template_dir = get_template_directory();
		$plugin_template_directory = CIVICRM_DIRECTORY_PATH . 'assets/templates/theme';

		// construct stack
		$stack = array( $template_dir, $parent_template_dir, $plugin_template_directory );

		/**
		 * Allow stack to be filtered.
		 *
		 * @since 0.1
		 *
		 * @param array $stack The default template stack.
		 * @return array $stack The filtered template stack.
		 */
		$stack = apply_filters( 'civicrm_directory_template_stack', $stack );

		// sanity check
		$stack = array_unique( $stack );

		// --<
		return $stack;

	}



} // class ends



/**
 * Render the listing section for a directory.
 *
 * @since 0.1.1
 */
function civicrm_directory_listing() {

	// render browse section now
	civicrm_directory()->template->insert_markup();

}



/**
 * Echoes the permalink for a directory.
 *
 * @since 0.2.2
 *
 * @param int $post_id The numeric ID of the directory.
 */
function civicrm_directory_url( $post_id = null ) {

	// echo permalink
	echo civicrm_directory_url_get( $post_id );

}



/**
 * Return the permalink for a directory.
 *
 * @since 0.2.2
 *
 * @param int $post_id The numeric ID of the directory.
 * @return str $url The permalink for the directory.
 */
function civicrm_directory_url_get( $post_id = null ) {

	// use current post if none passed
	if ( is_null( $post_id ) ) $post_id = get_the_ID();

	// get permalink
	$url = esc_url( get_permalink( $post_id ) );

	// --<
	return $url;

}



/**
 * Echo the title of a directory.
 *
 * @since 0.2.2
 *
 * @param int $post_id The numeric ID of the directory.
 */
function civicrm_directory_title( $post_id = null ) {

	// echo title
	echo civicrm_directory_title_get( $post_id );

}



/**
 * Return the title of a directory.
 *
 * @since 0.2.2
 *
 * @param int $post_id The numeric ID of the directory.
 * @return str $url The name of the directory.
 */
function civicrm_directory_title_get( $post_id = null ) {

	// pass to template object
	$title = civicrm_directory()->template->get_the_title( $post_id );

	// --<
	return $title;

}



/**
 * Echo the markup for a contact.
 *
 * @since 0.2.2
 */
function civicrm_directory_contact_details() {

	// echo contact details
	echo civicrm_directory_contact_details_get();

}



/**
 * Gets the markup for a contact.
 *
 * @since 0.2.2
 *
 * @return $markup The rendered markup for a contact.
 */
function civicrm_directory_contact_details_get() {

	// render browse section now
	return civicrm_directory()->template->the_contact();

}



