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

    $time_start = microtime( true );

	$log['filename'] = date( 'Y-m-d-H-i-s' ) . '.zip';

	update_option( 'hmbkp_running', $log['filename'] );

	// Raise the memory limit
    ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', '256M' ) );

	// start the log
    $log['logfile'] = array();

	// Create a temporary directory for this backup
    $backup_tmp_dir = hmbkp_create_tmp_dir( $log );

	// Backup database
    hmbkp_backup_mysql( $backup_tmp_dir, $log );

	// Backup files
	hmbkp_backup_files( $backup_tmp_dir, $log, $log['filename'] );

	// Delete any old backup files
    hmbkp_delete_old_backups( $log );

    $log['logfile'][] = sprintf( __( 'Backup done at %d', 'hmbkp' ), hmbkp_timestamp() );
    $log['logfile'][] = sprintf( __( 'Backup was running for %d Seconds', 'hmbkp' ), round( microtime( true ) - $time_start, 2 ) );

    hmbkp_write_log( $log );

    delete_option( 'hmbkp_running' );

}

/**
 * Deletes old backup files
 *
 * @see hmbkp_set_defaults for default number of backups
 * to be kept.
 */
function hmbkp_delete_old_backups( $log ) {

    $files = hmbkp_get_backups();

    if ( count( $files ) <= get_option( 'hmbkp_max_backups' ) )
    	return;

    $unlinkcount = 0;

    foreach ( $files as $key => $f ) :

        if ( ( $key + 1 ) > get_option( 'hmbkp_max_backups' ) ) :
        	hmbkp_delete_backup( base64_encode( $f['file'] ) );
        	$unlinkcount++;

        endif;

    endforeach;

    if ( $unlinkcount )
    	$log['logfile'][] = sprintf( __( '%d old backup deleted.', 'hmbkp' ), $unlinkcount );

    else
    	$log['logfile'][] = __( 'No old backups to delete.', 'hmbkp' );

}

/**
 * Returns an array of backup files
 *
 * @todo support when backups directory changes
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
 * Fire the backup on schedule
 *
 * @param mixed $options
 */
function hmbkp_schedule_backup( $options ) {
	hmbkp_do_backup();
}

/**
 * Delete a backup file and it's associated logs
 *
 * @param $file base64 encoded filename
 */
function hmbkp_delete_backup( $file ) {

	$file = base64_decode( $file );

	// Delete the file
	if ( strpos( $file, hmbkp_path() ) !== false || strpos( $file, WP_CONTENT_DIR . '/backups' ) !== false )
	  unlink( $file );

	// Handle changed backups directory
	$log = str_replace( hmbkp_path(), trailingslashit( hmbkp_path() ) . 'logs/', $file );
	$log = str_replace( WP_CONTENT_DIR . '/backups', WP_CONTENT_DIR . '/backups/logs/', $log );

	// Delete the log
	if ( file_exists( $log . '.log' ) )
		unlink( $log . '.log' );

	// Delete the mysqldump log
	if ( file_exists( str_replace( '.zip', '', $log ) . '-mysqldump.log' ) )
		unlink( str_replace( '.zip', '', $log ) . '-mysqldump.log' );

}


function hmbkp_create_tmp_dir( $log ) {

    // Temporary directory name
    $backup_tmp_dir = trailingslashit( hmbkp_path() ) . date( 'Y-m-d-H-i-s' );

    $log['logfile'][] = __( 'Backup starting at', 'hmbkp' ) . ' ' . hmbkp_timestamp() ;

	// Create the temp backup directory
    if ( !is_dir( $backup_tmp_dir ) ) :

    	if ( !mkdir( $backup_tmp_dir, 0777 ) )
    		$log['logfile'][] = sprintf( __( 'Backup temporary directory %s could not be created', 'hmbkp' ), $backup_tmp_dir );

    	else
    		$log['logfile'][] = sprintf( __( 'Backup temporary directory %s created', 'hmbkp' ), $backup_tmp_dir );

    else :
    	$log['logfile'][] = sprintf( __( 'Backup temporary directory %s exists.', 'hmbkp' ), $backup_tmp_dir );

    endif;

    hmbkp_write_log( $log );

    return $backup_tmp_dir;

}

function hmbkp_is_in_progress() {
	return (bool) get_option( 'hmbkp_running' );
}