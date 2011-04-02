<?php

/**
 * Backup database and files
 *
 * Creates a temporary directory containing a copy of all files
 * and a dump of the database. Then zip that up and delete the temporary files
 *
 * @uses hmbkp_backup_mysql
 * @uses hmbkp_backup_files
 * @uses hmbkp_delete_old_backups
 */
function hmbkp_do_backup() {

    $time_start = date( 'Y-m-d-H-i-s' );

	$filename = $time_start . '.zip';
	$filepath = trailingslashit( hmbkp_path() ) . $filename;

	update_option( 'hmbkp_running', $time_start );

	// Raise the memory limit
    ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', '256M' ) );
    set_time_limit( 300 );

	// Create a temporary directory for this backup
    $backup_tmp_dir = hmbkp_create_tmp_dir( $time_start );

	// Backup database
	if ( ( defined( 'HMBKP_FILES_ONLY' ) && !HMBKP_FILES_ONLY ) || !defined( 'HMBKP_FILES_ONLY' ) )
	    hmbkp_backup_mysql( $backup_tmp_dir );

	// Backup files
	if ( ( defined( 'HMBKP_DATABASE_ONLY' ) && !HMBKP_DATABASE_ONLY ) || !defined( 'HMBKP_DATABASE_ONLY' ) )
		hmbkp_backup_files( $backup_tmp_dir );
	
	// Zip up the files
	hmbkp_archive_files( $backup_tmp_dir, $filepath );
	
	// Remove the temporary directory
	hmbkp_rmdirtree( $backup_tmp_dir );
	
	// Delete any old backup files
    hmbkp_delete_old_backups();

    delete_option( 'hmbkp_running' );
    
    update_option( 'hmbkp_complete', true );

}

/**
 * Deletes old backup files
 */
function hmbkp_delete_old_backups() {

    $files = hmbkp_get_backups();

    if ( count( $files ) <= hmbkp_max_backups() )
    	return;

    foreach ( $files as $key => $f )
        if ( ( $key + 1 ) > hmbkp_max_backups() )
        	hmbkp_delete_backup( base64_encode( $f['file'] ) );

}

/**
 * Returns an array of backup files
 */
function hmbkp_get_backups() {

    $files = array();

    $hmbkp_path = hmbkp_path();

    if ( !is_writable( $hmbkp_path ) )
    	return;

    if ( $handle = opendir( $hmbkp_path ) ) :

    	while ( false !== ( $file = readdir( $handle ) ) )
    		if ( ( substr( $file, 0, 1 ) != '.' ) && !is_dir( trailingslashit( $hmbkp_path ) . $file ) )
    			$files[] = array( 'file' => trailingslashit( $hmbkp_path ) . $file, 'filename' => $file);

    	closedir( $handle );

    endif;

    if ( count( $files ) < 1 )
    	return;

    foreach ( $files as $key => $row )
    	$filename[$key] = $row['filename'];

    array_multisort( $filename, SORT_DESC, $files );

    return $files;
}

/**
 * Delete a backup file
 *
 * @param $file base64 encoded filename
 */
function hmbkp_delete_backup( $file ) {

	$file = base64_decode( $file );

	// Delete the file
	if ( strpos( $file, hmbkp_path() ) !== false || strpos( $file, WP_CONTENT_DIR . '/backups' ) !== false )
	  unlink( $file );

}

/**
 * Create and return the path to the tmp directory
 * 
 * @param string $date
 * @return string
 */
function hmbkp_create_tmp_dir( $date ) {

    $backup_tmp_dir = trailingslashit( hmbkp_path() ) . $date;
    
    if ( !is_dir( $backup_tmp_dir ) )
		mkdir( $backup_tmp_dir );

    return $backup_tmp_dir;

}

/**
 * Check if a backup is running
 * 
 * @return bool
 */
function hmbkp_is_in_progress() {
	return (bool) get_option( 'hmbkp_running' );
}