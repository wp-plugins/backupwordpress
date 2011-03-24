<?php

/**
 * hmbkp_set_defaults function.
 *
 * @return void
 */
function hmbkp_set_defaults() {

    update_option( 'hmbkp_max_backups', 10 );

	hmbkp_default_schedule();
}

/**
 * hmbkp_default_schedules function.
 *
 * @return void
 */
function hmbkp_default_schedule() {
	wp_schedule_event( strtotime( '2300' ), 'hmbkp_daily', 'hmbkp_schedule_backup_hook' );
}


/**
 * hmbkp_path function.
 */
function hmbkp_path() {

	$path = get_option( 'hmbkp_path' );

	// Allow the backups path to be defined
	if ( defined( 'HMBKP_PATH' ) && HMBKP_PATH )
		$path = HMBKP_PATH;

	// If the dir doesn't exist or isn't writable then use wp-content/backups instead
	if ( ( !$path || !is_writable( $path ) ) && $path != WP_CONTENT_DIR . '/backups' ) :
    	$path = WP_CONTENT_DIR . '/backups';
		update_option( 'hmbkp_path', $path );
	endif;

	// Create the backups directory if it doesn't exist
	if ( is_writable( WP_CONTENT_DIR ) && !is_dir( $path ) )
		mkdir( $path, 0755 );

	// Secure the directory with a .htaccess file
	$htaccess = $path . '/.htaccess';

	if ( !file_exists( $htaccess ) && is_writable( $path ) ) :
		require_once( ABSPATH . '/wp-admin/includes/misc.php' );
		insert_with_markers( $htaccess, 'BackUpWordPress', array( 'deny from all' ) );
	endif;

    return $path;
}