<?php

/**
 * hmbkp_backquote function.
 *
 * Add backquotes to tables and db-names inSQL queries. Taken from phpMyAdmin.
 *
 * @access public
 * @param mixed $a_name
 * @return void
 */
function hmbkp_backquote( $a_name ) {

    if ( !empty( $a_name ) && $a_name != '*' ) :

    	if ( is_array( $a_name ) ) :
    		$result = array();
    		reset( $a_name );

    		while ( list( $key, $val ) = each( $a_name ) )
    			$result[$key] = '`' . $val . '`';

    		return $result;

    	else :
    		return '`' . $a_name . '`';

    	endif;

    else :
    	return $a_name;
    endif;
}

/**
 * hmbkp_make_sql function.
 *
 * Reads the Database table in $table and creates
 * SQL Statements for recreating structure and data
 * Taken partially from phpMyAdmin and partially from
 * Alain Wolf, Zurich - Switzerland
 * Website: http://restkultur.ch/personal/wolf/scripts/db_backup/
 *
 * @access public
 * @param string $path
 * @param mixed $sql_file
 * @param mixed $table
 * @param mixed $log
 * @return void
 */
function hmbkp_make_sql( $path, $sql_file, $table, $log ) {

    // Add SQL statement to drop existing table
    $sql_file = "\n";
    $sql_file .= "\n";
    $sql_file .= "#\n";
    $sql_file .= "# Delete any existing table " . hmbkp_backquote( $table ) . "\n";
    $sql_file .= "#\n";
    $sql_file .= "\n";
    $sql_file .= "DROP TABLE IF EXISTS " . hmbkp_backquote( $table ) . ";\n";

    /* Table Structure */

    // Comment in SQL-file
    $sql_file .= "\n";
    $sql_file .= "\n";
    $sql_file .= "#\n";
    $sql_file .= "# Table structure of table " . hmbkp_backquote( $table ) . "\n";
    $sql_file .= "#\n";
    $sql_file .= "\n";

    // Get table structure
    $query = 'SHOW CREATE TABLE ' . hmbkp_backquote( $table );
    $result = mysql_query( $query, $GLOBALS['db_connect'] );

    if ( $result == false ) :
    	$log['logfile'][] = __( 'Error getting table structure of ', 'hmbkp' ) . $table . '! ' . mysql_errno() . ': ' . mysql_error();
    	hmbkp_write_log( $log );

    else :
    	if ( mysql_num_rows( $result ) > 0 ) :
    		$sql_create_arr = mysql_fetch_array( $result );
    		$sql_file .= $sql_create_arr[1];
    	endif;

    	mysql_free_result( $result );
    	$sql_file .= ' ;';

    endif;

    /* Table Contents */

    // Get table contents
    $query = 'SELECT * FROM ' . hmbkp_backquote( $table );
    $result = mysql_query( $query, $GLOBALS['db_connect'] );

    if ( $result == false ) :
    	$log['logfile'][] = __( 'Error getting records of ', 'hmbkp' ) . $table . '! ' . mysql_errno() . ': ' . mysql_error();
    	hmbkp_write_log( $log );

    else :
    	$fields_cnt = mysql_num_fields( $result );
    	$rows_cnt   = mysql_num_rows( $result );
    endif;

    // Comment in SQL-file
    $sql_file .= "\n";
    $sql_file .= "\n";
    $sql_file .= "#\n";
    $sql_file .= "# Data contents of table " . $table . " (" . $rows_cnt . " records)\n";
    $sql_file .= "#\n";

    // Checks whether the field is an integer or not
    for ( $j = 0; $j < $fields_cnt; $j++ ) :
    	$field_set[$j] = hmbkp_backquote( mysql_field_name( $result, $j ) );
    	$type = mysql_field_type( $result, $j );

    	if ( $type == 'tinyint' || $type == 'smallint' || $type == 'mediumint' || $type == 'int' || $type == 'bigint'  ||$type == 'timestamp')
    		$field_num[$j] = true;
    	else
    		$field_num[$j] = false;

    endfor;

    // Sets the scheme
    $entries = 'INSERT INTO ' . hmbkp_backquote($table) . ' VALUES (';
    $search   = array( '\x00', '\x0a', '\x0d', '\x1a' );  //\x08\\x09, not required
    $replace  = array( '\0', '\n', '\r', '\Z' );
    $current_row = 0;
    $batch_write = 0;

    while ( $row = mysql_fetch_row( $result ) ) :
    	$current_row++;

    	// build the statement
    	for ( $j = 0; $j < $fields_cnt; $j++ ) :

    		if ( !isset($row[$j] ) ) :
    			$values[]     = 'NULL';

    		elseif ( $row[$j] == '0' || $row[$j] != '' ) :

    		    // a number
    		    if ( $field_num[$j] )
    		    	$values[] = $row[$j];

    		    else
    		    	$values[] = "'" . str_replace( $search, $replace, hmbkp_sql_addslashes( $row[$j] ) ) . "'";

    		else :
    			$values[] = "''";
    		endif;

    	endfor;

    	$sql_file .= " \n" . $entries . implode( ', ', $values ) . ") ;";

    	// write the rows in batches of 100
    	if ( $batch_write == 100 ) :
    		$batch_write = 0;
    		hmbkp_write_sql( $path, $sql_file, $log  );
    		$sql_file = '';
    	endif;

    	$batch_write++;

    	unset( $values );
   
    endwhile;

    mysql_free_result( $result );

    // Create footer/closing comment in SQL-file
    $sql_file .= "\n";
    $sql_file .= "#\n";
    $sql_file .= "# End of data contents of table " . $table . "\n";
    $sql_file .= "# --------------------------------------------------------\n";
    $sql_file .= "\n";
    
	hmbkp_write_sql( $path, $sql_file, $log );

}

/**
 * hmbkp_sql_addslashes function.
 *
 * Better addslashes for SQL queries.
 * Taken from phpMyAdmin.
 *
 * @access public
 * @param string $a_string. (default: '')
 * @param bool $is_like. (default: false)
 * @return void
 */
function hmbkp_sql_addslashes( $a_string = '', $is_like = false ) {

    if ( $is_like )
    	$a_string = str_replace( '\\', '\\\\\\\\', $a_string );

    else
    	$a_string = str_replace( '\\', '\\\\', $a_string );

    $a_string = str_replace( '\'', '\\\'', $a_string );

    return $a_string;
}

/**
 * hmbkp_mysql function.
 *
 * @access public
 * @param mixed $path
 * @param mixed $log
 * @return void
 */
function hmbkp_backup_mysql_fallback( $path, $log ) {

    if ( !$GLOBALS['db_connect'] = @mysql_pconnect( DB_HOST, DB_USER, DB_PASSWORD ) ) :
    	$log['logfile'][] = __( 'Could not connect to MySQL server! ', 'hmbkp' ) . mysql_error();
    	hmbkp_write_log( $log );
    endif;

    $log['logfile'][] = __( 'MySQL server connected successfully ', 'hmbkp' );
    hmbkp_write_log( $log );

    mysql_select_db( DB_NAME, $GLOBALS['db_connect'] );

    // Begin new backup of MySql
    $tables = mysql_list_tables( DB_NAME );

    if ( !isset($tables) > 0 ) :
    	$log['logfile'][] = __( 'Could not select db ', 'hmbkp') . DB_NAME;
    	hmbkp_write_log( $log );
    endif;

    $sql_file  = "# WordPress : " . get_bloginfo( 'url' ) . " MySQL database backup\n";
    $sql_file .= "#\n";
    $sql_file .= "# Generated: " . date( 'l j. F Y H:i T' ) . "\n";
    $sql_file .= "# Hostname: " . DB_HOST . "\n";
    $sql_file .= "# Database: " . hmbkp_backquote( DB_NAME ) . "\n";
    $sql_file .= "# --------------------------------------------------------\n";

    for ( $i = 0; $i < mysql_num_rows( $tables ); $i++ ) :

    	$curr_table = mysql_tablename( $tables, $i );

    	// Increase script execution time-limit to 15 min for every table.
    	if ( !ini_get( 'safe_mode' ) )
    		@set_time_limit( 15 * 60 );

    	// Create the SQL statements
    	$sql_file .= "# --------------------------------------------------------\n";
    	$sql_file .= "# Table: " . hmbkp_backquote( $curr_table ) . "\n";
    	$sql_file .= "# --------------------------------------------------------\n";
    	hmbkp_make_sql( $path, $sql_file, $curr_table, $log );

    	$log['logfile'][] = 'sql-dump for ' . $curr_table . ' created';
    	hmbkp_write_log( $log );
    endfor;

    if ( !file_exists( $path . '/wordpress.sql' ) )
    	$log['logfile'][] = __( 'SQL Dump could not be created.', 'hmbkp' );

    else
    	$log['logfile'][] = __( 'SQL Dump created.', 'hmbkp' );

    hmbkp_write_log( $log );

}

/**
 * hmbkp_write_sql function.
 *
 * @param mixed $sqldir
 * @param mixed $sql
 * @param mixed $log
 */
function hmbkp_write_sql( $sqldir, $sql, $log ) {

    $sqlname = $sqldir . '/wordpress.sql';

    // Actually write the sql file
    if ( is_writable( $sqlname ) || !file_exists( $sqlname ) ) :

    	if ( !$handle = fopen( $sqlname, 'a' ) ) :
    		$log['logfile'][] =  __( 'SQLfile could not be opened for writing: ', 'hmbkp' ) . $sqlname;
    		hmbkp_write_log( $log );
    		return;
    	endif;

    	if ( !fwrite( $handle, $sql ) ) :
    		$log['logfile'][] =  __( 'SQLfile not writable: ', 'hmbkp' ) . $sqlname;
    		hmbkp_write_log( $log );
    		return;
    	endif;

    	$log['logfile'][] =  __( 'successfully written to SQLfile: ', 'hmbkp' ) . $sqlname;
    	hmbkp_write_log( $log );
    	fclose( $handle );

    	return true;

    else :
    	$log['logfile'][] =  __( 'SQLfile not writable: ', 'hmbkp' ) . $sqlname;
    	hmbkp_write_log( $log );

    endif;
}