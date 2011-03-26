<?php

/**
 * Copy the whole site to the temporary directory and
 * then zip it all up
 *
 * @param string $backup_tmp_dir
 * @param array $log
 * @param string $backup_filename
 * @return void
 */
function hmbkp_backup_files( $backup_tmp_dir, $log, $backup_filename ) {

	$backup_filepath = trailingslashit( hmbkp_path() ) . $backup_filename;
    $wordpress_files = $backup_tmp_dir . '/wordpress_files';

    if ( !is_dir( $wordpress_files ) ) :

        if ( !mkdir( $wordpress_files, 0777 ) )
        	$log['logfile'][] = sprintf( __( 'The temporary directory %s could not be created', 'hmbkp' ), $wordpress_files );
        else
        	$log['logfile'][] = sprintf( __( 'Temporary directory created', 'hmbkp' ), $wordpress_files );

        hmbkp_write_log( $log );

    endif;

    // Copy the whole site to the temporary directory
    $files = hmbkp_ls( hmbkp_conform_dir( ABSPATH ) );

    $files_copied = $subdirs_created = 0;
    $i = 1;

    foreach ( (array) $files as $f ) :

        if ( is_dir( $f ) ) :

        	if ( !mkdir( $wordpress_files . hmbkp_conform_dir( $f, true ), 0755 ) ) :

        		if ( !is_dir( $wordpress_files . hmbkp_conform_dir( $f, true ) ) ) {
        			$log['logfile'][] = __( 'Failed to make directory', 'hmbkp' ) . ': ' . $f;
        		}

        	else
        		$subdirs_created++;

        	endif;

        elseif ( file_exists( $f ) ) :

        	$files_copied++;

        	if ( file_exists( $wordpress_files . hmbkp_conform_dir( $f, true ) ) )
        		unlink( $wordpress_files . hmbkp_conform_dir( $f, true ) );

        	// Copy the file
        	if ( !copy( $f, $wordpress_files . hmbkp_conform_dir( $f, true ) ) )
        		$log['logfile'][] = __( 'Failed to copy file', 'hmbkp' ) . ': ' . $f;

        endif;

        $i++;
    endforeach;

    $log['logfile'][] = $subdirs_created . ' ' . __( 'temporary sub-directories copied sucessfully', 'hmbkp' );
    $log['logfile'][] = $files_copied . ' ' . __( 'temporary files copied sucessfully', 'hmbkp' );

	// Zip up the files
	hmbkp_archive_files( $backup_tmp_dir, $backup_filepath );

    // Make sure the archive exists
    if ( !file_exists( $backup_filepath ) )
    	$log['logfile'][] = __( 'Failed to create backup archive', 'hmbkp' ) . ' ' . $backup_filename;

    else
    	$log['logfile'][] = __( 'Archive created successfully:', 'hmbkp' ) . ' ' . $backup_filename;

    $log['logfile'][] = count( hmbkp_rmdirtree( $backup_tmp_dir ) ) . ' ' . __( 'Temporary file and directories deleted successfully', 'hmbkp' );

    hmbkp_write_log( $log );

}

/**
 * Zip up all the files in the tmp directory.
 *
 * Attempts to use the shell zip command, if
 * thats not available then it fallsback on
 * PHP zip classes.
 *
 * @param string $backup_tmp_dir
 * @param string $backup_filepath
 * @return void
 */
function hmbkp_archive_files( $backup_tmp_dir, $backup_filepath ) {

	// Do we have the path to the zip command
	if ( hmbkp_zip_path() )
		shell_exec( 'cd ' . escapeshellarg( $backup_tmp_dir ) . ' && zip -r ' . escapeshellarg( $backup_filepath ) . ' ./' );

	else
		hmbkp_archive_files_fallback( $backup_tmp_dir, $backup_filepath );

}

/**
 * Attempt to work out the path to the zip command
 *
 * Can be overridden by defining HMBKP_ZIP_PATH in
 * wp-config.php.
 *
 * @return string $path on success, empty string on failure
 */
function hmbkp_zip_path() {

	if ( !hmbkp_shell_exec_available() )
		return false;

	$path = '';

	// List of possible zip locations
	$zip_locations = array(
		'zip',
		'/usr/bin/zip'
	);

	// Allow the path to be overridden
	if ( defined( 'HMBKP_ZIP_PATH' ) && HMBKP_ZIP_PATH )
		array_unshift( $zip_locations, HMBKP_ZIP_PATH );

 	// If we don't have a path set
 	if ( !$path = get_option( 'hmbkp_zip_path' ) ) :

		// Try to find out where zip is
		foreach ( $zip_locations as $location )
	 		if ( shell_exec( 'which ' . $location ) )
 				$path = $location;

		// Save it for later
 		if ( $path )
			update_option( 'hmbkp_zip_path', $path );

	endif;

	// Check again incase the saved path has stopped working for some reason
	if ( $path && !shell_exec( 'which ' . $path ) ) :
		delete_option( 'hmbkp_zip_path' );
		return hmbkp_zip_path();

	endif;

	return $path;

}