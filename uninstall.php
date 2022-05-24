<?php
/**
 * CiviCRM Directory Uninstaller
 *
 * Handles uninstallation.
 *
 * @package CiviCRM_Directory
 * @since 0.1
 */

// Kick out if uninstall not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Delete installed flag.
delete_option( 'civicrm_directory_version' );

// Delete settings.
delete_option( 'civicrm_directory_settings' );
