<?php

/**
 * Create the mysql backup
 *
 * Uses mysqldump if available, fallsback to PHP
 * if not.
 *
 * @param string $backup_tmp_dir
 * @param array $log
 * @return void
 */
function hmbkp_backup_mysql( $backup_tmp_dir, $log ) {

	// Use mysqldump if we can
	if ( hmbkp_mysqldump_path() )
		shell_exec(
			escapeshellarg( hmbkp_mysqldump_path() )
			. ' --no-create-db '
			.' -u ' . escapeshellarg( DB_USER )
			.' -p'  . escapeshellarg( DB_PASSWORD )
			.' -h ' . escapeshellarg( DB_HOST )
			.' -r ' . escapeshellarg( $backup_tmp_dir . '/wordpress.sql' ) . ' ' . escapeshellarg( DB_NAME )
			. ' 1> ' . escapeshellarg( str_replace( hmbkp_path(), trailingslashit( hmbkp_path() ) . 'logs/', $backup_tmp_dir ) . '-mysqldump.log' ) .' 2>&1'
		);

	// Fallback to using PHP if not
	else
		hmbkp_backup_mysql_fallback( $backup_tmp_dir, $log );

}

/**
 * Attempt to work out the path to mysqldump
 *
 * Can be overridden by defining HMBKP_MYSQLDUMP_PATH in
 * wp-config.php.
 *
 * @return string $path on success, empty string on failure
 */
function hmbkp_mysqldump_path() {

	if ( !hmbkp_shell_exec_available() )
		return false;

	$path = '';

	// List of possible mysqldump locations
	$mysqldump_locations = array(
		'mysqldump',
		'/usr/local/bin/mysqldump',
		'/usr/local/mysql/bin/mysqldump',
		'/usr/mysql/bin/mysqldump',
		'/usr/bin/mysqldump',
		'/opt/local/lib/mysql6/bin/mysqldump',
		'/opt/local/lib/mysql5/bin/mysqldump',
		'/opt/local/lib/mysql4/bin/mysqldump'
	);

	// Allow the path to be overridden
	if ( defined( 'HMBKP_MYSQLDUMP_PATH' ) && HMBKP_MYSQLDUMP_PATH )
		array_unshift( $mysqldump_locations, HMBKP_MYSQLDUMP_PATH );

 	// If we don't have a path set
 	if ( !$path = get_option( 'hmbkp_mysqldump_path' ) ) :

		// Try to find out where mysqldump is
		foreach ( $mysqldump_locations as $location )
	 		if ( shell_exec( 'which ' . $location ) )
 				$path = $location;

		// Save it for later
 		if ( $path )
			update_option( 'hmbkp_mysqldump_path', $path );

	endif;

	// Check again incase the saved path has stopped working for some reason
	if ( $path && !shell_exec( 'which ' . $path ) ) :
		delete_option( 'hmbkp_mysqldump_path' );
		return hmbkp_mysqldump_path();

	endif;

	return $path;

}