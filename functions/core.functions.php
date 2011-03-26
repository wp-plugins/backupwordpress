<?php

/**
 * Setup the default options on plugin activation
 *
 * @return void
 */
function hmbkp_activate() {
	
	hmbkp_set_defaults();
}

/**
 * Cleanup on plugin deactivation
 *
 * Removes options and clears all cron schedules
 *
 * @todo remove all hmbkp_ options
 * @return void
 */
function hmbkp_deactivate() {

	// Delete options
	$options = array(
		'hmbkp_zip_path',
		'hmbkp_mysqldump_path',
		'hmbkp_path',
		'hmbkp_max_backups',
		'hmbkp_running',
		'_transient_hmbkp_estimated_filesize',
		'_transient_timeout_hmbkp_estimated_filesize'
	);

	foreach ( $options as $option )
		delete_option( $option );


	// Clear crons
	wp_clear_scheduled_hook( 'hmbkp_schedule_backup_hook' );

}


/**
 * Handles anything that needs to be
 * done when the plugin is updated
 *
 * @return void
 */
function hmbkp_update() {

	if ( !get_option( 'hmbkp_max_backups' ) )
		hmbkp_set_defaults();

	// Update from backUpWordPress
	if ( get_option( 'bkpwp_max_backups' ) ) :

		// Carry over the custom path
		if ( $legacy_path = get_option( 'bkpwppath' ) )
			update_option( 'hmbkp_path', $legacy_path );

		// Options to remove
		$legacy_options = array(
			'bkpwp_archive_types',
			'bkpwp_automail_from',
			'bkpwp_domain',
			'bkpwp_domain_path',
			'bkpwp_easy_mode',
			'bkpwp_excludelists',
			'bkpwp_install_user',
			'bkpwp_listmax_backups',
			'bkpwp_max_backups',
			'bkpwp_presets',
			'bkpwp_reccurrences',
			'bkpwp_schedules',
			'bkpwp_calculation',
			'bkpwppath',
			'bkpwp_status_config',
			'bkpwp_status'
		);

		foreach ( $legacy_options as $option )
			delete_option( $option );

	    global $wp_roles;

		$wp_roles->remove_cap( 'administrator','manage_backups' );
		$wp_roles->remove_cap( 'administrator','download_backups' );

		wp_clear_scheduled_hook( 'bkpwp_schedule_bkpwp_hook' );

	endif;

}

/**
 * Simply wrapper function for creating timestamps
 *
 * @return timestamp
 */
function hmbkp_timestamp() {
	return date( get_option( 'date_format' ) ) . ' ' . date( 'H:i:s' );
}

/**
 * Sanitize a directory path
 *
 * @param string $dir
 * @param bool $rel. (default: false)
 * @return void
 */
function hmbkp_conform_dir( $dir, $rel = false ) {

	$dir = str_replace( '\\', '/', $dir );
	$dir = str_replace( '//', '/', $dir );

	$dir = untrailingslashit( $dir );

	if ( $rel == true )
		$dir = str_replace( hmbkp_conform_dir( ABSPATH ), '', $dir );

	return $dir;
}

/**
 * Take a file size and return a human readable
 * version
 *
 * @param int $size
 * @param string $unit. (default: null)
 * @param string $retstring. (default: null)
 * @param bool $si. (default: true)
 * @return int
 */
function hmbkp_size_readable( $size, $unit = null, $retstring = '%01.2f %s', $si = true ) {

	// Units
	if ( $si === true ) :
		$sizes = array( 'B', 'kB', 'MB', 'GB', 'TB', 'PB' );
		$mod   = 1000;

	else :
		$sizes = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
		$mod   = 1024;

	endif;

	$ii = count( $sizes ) - 1;

	// Max unit
	$unit = array_search( (string) $unit, $sizes );

	if ( is_null( $unit ) || $unit === false )
		$unit = $ii;

	// Loop
	$i = 0;

	while ( $unit != $i && $size >= 1024 && $i < $ii ) {
		$size /= $mod;
		$i++;
	}

	return sprintf( $retstring, $size, $sizes[$i] );
}

/**
 * Add daily as a cron schedule choice
 *
 * @param array $recc
 * @return array $recc
 */
function hmbkp_more_reccurences( $recc ) {

	$hmbkp_reccurrences = array(
	    'hmbkp_daily' => array( 'interval' => 86400, 'display' => 'every day' )
	);

	return array_merge( $recc, $hmbkp_reccurrences );
}

/**
 * Send a flie to the browser for download
 *
 * @param string $path
 * @return void
 */
function hmbkp_send_file( $path ) {

	session_write_close();

	ob_end_clean();

	if ( !is_file( $path ) || connection_status() != 0 )
		return false;

	// Overide max_execution_time
	set_time_limit( 0 );

	$name = basename( $path );

	// Filenames in IE containing dots will screw up the filename unless we add this
	if ( strstr( $_SERVER['HTTP_USER_AGENT'], 'MSIE' ) )
		$name = preg_replace( '/\./', '%2e', $name, substr_count( $name, '.' ) - 1 );

	// Force
	header( 'Cache-Control: ' );
	header( 'Pragma: ' );
	header( 'Content-Type: application/octet-stream' );
	header( 'Content-Length: ' . (string) ( filesize( $path ) ) );
	header(	'Content-Disposition: attachment; filename=" ' . $name . '"' );
	header( 'Content-Transfer-Encoding: binary\n' );

	if ( $file = fopen( $path, 'rb' ) ) :

		while ( ( !feof( $file ) ) && ( connection_status() == 0) ) :

			print( fread( $file, 1024 * 8 ) );
			flush();

		endwhile;

		fclose( $file );

	endif;

	return ( connection_status() == 0 ) and !connection_aborted();
}

/**
 * Takes a directory and returns an array of files.
 * Does traverse sub-directories
 *
 * @param string $dir
 * @param array $files. (default: array())
 * @return arrat $files
 */
function hmbkp_ls( $dir, $files = array() ) {

	$d = opendir( $dir );

	if ( strpos( $dir, hmbkp_path() ) !== false )
		return $files;

	while ( $file = readdir( $d ) ) :

		// Ignore current dir and containing dir as well as files in the backups dir
		if ( $file == '.' || $file == '..' || strpos( trailingslashit( $dir ) . $file, hmbkp_path() ) !== false || strpos( trailingslashit( $dir ) . $file, WP_CONTENT_DIR . '/backups' ) !== false )
			continue;

		$files[] = trailingslashit( $dir ) . $file;

		if ( is_dir( trailingslashit( $dir ) . $file ) )
			$files = hmbkp_ls( trailingslashit( $dir ) . $file, $files );

	endwhile;

	return $files;
}

/**
 * Recursively delete a directory including
 * all the files and sub-directories.
 *
 * @param string $dirname
 */
function hmbkp_rmdirtree( $dirname ) {

    if ( !is_dir( $dirname ) )
    	return false;

    $result = array();

    $dirname = trailingslashit( $dirname );

    $handle = opendir( $dirname );

    while ( false !== ( $file = readdir( $handle ) ) ) :

        // Ignore . and ..
        if ( $file != '.' && $file != '..' ) :

        	$path = $dirname . $file;

        	// Recurse if subdir, Delete if file
        	if ( is_dir( $path ) ) :
        		$result = array_merge( $result, hmbkp_rmdirtree( $path ) );

        	else :
        		unlink( $path );
        		$result[] .= $path;

        	endif;

        endif;

    endwhile;

    closedir( $handle );

    rmdir( $dirname );

    $result[] .= $dirname;

    return $result;

}

/**
 * Calculate the size of the backup
 *
 * Doesn't currently take into account for
 * compression
 *
 * @return string
 */
function hmbkp_calculate() {

    ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', '256M' ) );

    // Check cache
    if ( $filesize = get_transient( 'hmbkp_estimated_filesize' ) )
    	return hmbkp_size_readable( $filesize );

    global $wpdb;

    $sql = 'SHOW TABLE STATUS FROM ' . DB_NAME;
    $res = $wpdb->get_results( $sql, ARRAY_A );
    $sum_free = $sum_data = $filesize = 0;

    foreach ( $res as $r ) :
    	$sum_free += $r['Data_free'];
    	$sum_data += $r['Data_length'];
    endforeach;

    // Get rid of any cached filesizes
    clearstatcache();

    $dir = hmbkp_conform_dir( ABSPATH );
    $files = hmbkp_ls( $dir );

    foreach ( $files as $f ) :
    	$str = hmbkp_conform_dir( $f, true );
    	$filesize += @filesize( $f );
    endforeach;

    // Cache in a transient for a day
    set_transient( 'hmbkp_estimated_filesize', $filesize,  86400 );

    return hmbkp_size_readable( $filesize );

}

/**
 * Check whether shell_exec has been disabled.
 *
 * @return bool
 */
function hmbkp_shell_exec_available() {

	$disable_functions = ini_get( 'disable_functions' );

	if ( strpos( $disable_functions, 'shell_exec' ) !== false )
		return false;

	return true;

}