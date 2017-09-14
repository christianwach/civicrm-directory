<?php /*
================================================================================
CiviCRM Directory Uninstaller
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====


--------------------------------------------------------------------------------
*/



// kick out if uninstall not called from WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();



// delete installed flag
delete_option( 'civicrm_directory_version' );

// delete settings
delete_option( 'civicrm_directory_settings' );



