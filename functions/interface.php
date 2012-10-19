<?php

/**
 * Displays a row in the manage backups table
 *
 * @param string $file
 */
function hmbkp_get_backup_row( $file, HMBKP_Scheduled_Backup $schedule ) {

	$encoded_file = urlencode( base64_encode( $file ) );
	$offset = current_time( 'timestamp' ) - time(); ?>

	<tr class="hmbkp_manage_backups_row<?php if ( file_exists( hmbkp_path() . '/.backup_complete' ) ) : ?> completed<?php unlink( hmbkp_path() . '/.backup_complete' ); endif; ?>">

		<th scope="row">
			<?php echo esc_attr( date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), @filemtime( $file ) + $offset ) ); ?>
		</th>

		<td class="code">
			<?php echo esc_attr( HMBKP_Scheduled_Backup::human_filesize( @filesize( $file ) ) ); ?>
		</td>

		<td><?php echo esc_attr( hmbkp_human_get_type( $file, $schedule ) ); ?></td>

		<td>

			<a href="<?php echo wp_nonce_url( admin_url( 'tools.php?page=' . HMBKP_PLUGIN_SLUG . '&amp;hmbkp_download_backup=' . $encoded_file . '&amp;hmbkp_schedule_id=' . $schedule->get_id() ), 'hmbkp-download_backup' ); ?>"><?php _e( 'Download', 'hmbkp' ); ?></a> |
			<a href="<?php echo wp_nonce_url( admin_url( 'tools.php?page=' . HMBKP_PLUGIN_SLUG . '&amp;hmbkp_delete_backup=' . $encoded_file . '&amp;hmbkp_schedule_id=' . $schedule->get_id() ), 'hmbkp-delete_backup' ); ?>" class="delete-action"><?php _e( 'Delete', 'hmbkp' ); ?></a>

		</td>

	</tr>

<?php }

/**
 * Displays admin notices for various error / warning
 * conditions
 *
 * @return void
 */
function hmbkp_admin_notices() {

	// If the backups directory doesn't exist and can't be automatically created
	if ( ! is_dir( hmbkp_path() ) ) :

	    function hmbkp_path_exists_warning() {
		    $php_user = exec( 'whoami' );
			$php_group = reset( explode( ' ', exec( 'groups' ) ) );
	    	echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress is almost ready.', 'hmbkp' ) . '</strong> ' . sprintf( __( 'The backups directory can\'t be created because your %1$s directory isn\'t writable, run %2$s or %3$s or create the folder yourself.', 'hmbkp' ), '<code>wp-content</code>', '<code>chown ' . $php_user . ':' . $php_group . ' ' . WP_CONTENT_DIR . '</code>', '<code>chmod 777 ' . WP_CONTENT_DIR . '</code>' ) . '</p></div>';
	    }
	    add_action( 'admin_notices', 'hmbkp_path_exists_warning' );

	endif;

	// If the backups directory exists but isn't writable
	if ( is_dir( hmbkp_path() ) && ! is_writable( hmbkp_path() ) ) :

	    function hmbkp_writable_path_warning() {
			$php_user = exec( 'whoami' );
			$php_group = reset( explode( ' ', exec( 'groups' ) ) );
	    	echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress is almost ready.', 'hmbkp' ) . '</strong> ' . sprintf( __( 'Your backups directory isn\'t writable, run %1$s or %2$s or set the permissions yourself.', 'hmbkp' ), '<code>chown -R ' . esc_attr( $php_user ) . ':' . esc_attr( $php_group ) . ' ' . esc_attr( hmbkp_path() ) . '</code>', '<code>chmod -R 777 ' . esc_attr( hmbkp_path() ) . '</code>' ) . '</p></div>';
	    }
	    add_action( 'admin_notices', 'hmbkp_writable_path_warning' );

	endif;

	// If safe mode is active
	if ( HM_Backup::is_safe_mode_active() ) :

	    function hmbkp_safe_mode_warning() {
	    	echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress has detected a problem.', 'hmbkp' ) . '</strong> ' . sprintf( __( '%1$s is running in %2$s. Please contact your host and ask them to disable %3$s.', 'hmbkp' ), '<code>PHP</code>', sprintf( '<a href="%1$s">%2$s</a>', __( 'http://php.net/manual/en/features.safe-mode.php', 'hmbkp' ), __( 'Safe Mode', 'hmbkp' ) ), '<code>' . __( 'Safe Mode', 'hmbkp' ) . '</code>' ) . '</p></div>';
	    }
	    add_action( 'admin_notices', 'hmbkp_safe_mode_warning' );

	endif;

	// If a custom backups directory is defined and it doesn't exist and can't be created
	if ( defined( 'HMBKP_PATH' ) && HMBKP_PATH && ! is_dir( HMBKP_PATH ) ) :

		function hmbkp_custom_path_exists_warning() {
			echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress has detected a problem.', 'hmbkp' ) . '</strong> ' . sprintf( __( 'Your custom backups directory %1$s doesn\'t exist and can\'t be created, your backups will be saved to %2$s instead.', 'hmbkp' ), '<code>' . esc_attr( HMBKP_PATH ) . '</code>', '<code>' . esc_attr( hmbkp_path() ) . '</code>' ) . '</p></div>';
		}
		add_action( 'admin_notices', 'hmbkp_custom_path_exists_warning' );

	endif;

	// If a custom backups directory is defined and exists but isn't writable
	if ( defined( 'HMBKP_PATH' ) && HMBKP_PATH && is_dir( HMBKP_PATH ) && ! is_writable( HMBKP_PATH ) ) :

		function hmbkp_custom_path_writable_notice() {
			echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress has detected a problem.', 'hmbkp' ) . '</strong> ' . sprintf( __( 'Your custom backups directory %1$s isn\'t writable, new backups will be saved to %2$s instead.', 'hmbkp' ), '<code>' . esc_attr( HMBKP_PATH ) . '</code>', '<code>' . esc_attr( hmbkp_path() ) . '</code>' ) . '</p></div>';
		}
		add_action( 'admin_notices', 'hmbkp_custom_path_writable_notice' );

	endif;

	// If there are any errors reported in the backup
	if ( hmbkp_backup_errors_message() ) :

		function hmbkp_backup_errors_notice() {
			echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress detected issues with your last backup.', 'hmbkp' ) . '</strong><a href="' . add_query_arg( 'action', 'hmbkp_dismiss_error' ) . '" style="float: right;" class="button">Dismiss</a></p>' . hmbkp_backup_errors_message() . '</div>';
		}
		add_action( 'admin_notices', 'hmbkp_backup_errors_notice' );

	endif;

	if ( ! empty( $_GET['reason'] ) ) :

		function hmbkp_backup_failed_notice() {
			echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress failed to perform the backup.', 'hmbkp' ) . '</strong> ' . __( 'You have likely hit a memory limit.', 'hmbkp' ) . '<a href="' . add_query_arg( 'action', 'hmbkp_dismiss_error' ) . '" style="float: right;" class="button">Dismiss</a></p></div>';
		}
		//add_action( 'admin_notices', 'hmbkp_backup_failed_notice' );

	endif;

}
add_action( 'admin_head', 'hmbkp_admin_notices' );

/**
 * Hook in an change the plugin description when BackUpWordPress is activated
 *
 * @param array $plugins
 * @return $plugins
 */
function hmbkp_plugin_row( $plugins ) {

	if ( isset( $plugins[HMBKP_PLUGIN_SLUG . '/plugin.php'] ) )
		$plugins[HMBKP_PLUGIN_SLUG . '/plugin.php']['Description'] = str_replace( 'Once activated you\'ll find me under <strong>Tools &rarr; Backups</strong>', 'Find me under <strong><a href="' . admin_url( 'tools.php?page=' . HMBKP_PLUGIN_SLUG ) . '">Tools &rarr; Backups</a></strong>', $plugins[HMBKP_PLUGIN_SLUG . '/plugin.php']['Description'] );

	return $plugins;

}
add_filter( 'all_plugins', 'hmbkp_plugin_row', 10 );

/**
 * Parse the json string of errors and
 * output as a human readable message
 *
 * @access public
 * @return null
 */
function hmbkp_backup_errors_message() {

	$message = '';

	foreach ( (array) json_decode( hmbkp_backup_errors() ) as $key => $errors )
		foreach ( $errors as $error )
			$message .= '<p><strong>' . $key . '</strong>: <code>' . implode( ':', (array) $error ) . '</code></p>';

	return $message;

}

/**
 * Display a html list of files
 *
 * @param HMBKP_Scheduled_Backup $schedule
 * @param mixed $excludes (default: null)
 * @param string $file_method (default: 'get_included_files')
 * @return void
 */
function hmbkp_file_list( HMBKP_Scheduled_Backup $schedule, $excludes = null, $file_method = 'get_included_files' ) {

	if ( ! is_null( $excludes ) )
		$schedule->set_excludes( $excludes );

	$files = $schedule->$file_method();

	if ( $files ) : ?>

	<ul class="hmbkp_file_list code">

		<?php foreach( $files as $file ) :

			if ( ! is_null( $excludes ) && strpos( $file, str_ireplace( $schedule->get_root(), '', $schedule->get_path() ) ) !== false )
				continue; ?>

			<?php if ( $file->isDir() ) { ?>

		<li title="<?php echo esc_attr( HM_Backup::conform_dir( trailingslashit( $file->getPathName() ) ) ); ?>"><?php echo trailingslashit( str_ireplace( HM_Backup::conform_dir( trailingslashit( $schedule->get_root() ) ), '', HM_Backup::conform_dir( $file->getPathName() ) ) ); ?></li>

			<?php } else { ?>

		<li title="<?php echo esc_attr( HM_Backup::conform_dir( $file->getPathName() ) ); ?>"><?php echo str_ireplace( HM_Backup::conform_dir( trailingslashit( $schedule->get_root() ) ), '', HM_Backup::conform_dir( $file->getPathName() ) ); ?></li>

			<?php }

		endforeach; ?>

	</ul>

	<?php endif;

}


/**
 * Get the human readable backup type in.
 *
 * @access public
 * @param string $type
 * @param HMBKP_Scheduled_Backup $schedule (default: null)
 * @return string
 */
function hmbkp_human_get_type( $type, HMBKP_Scheduled_Backup $schedule = null ) {

	if ( strpos( $type, 'complete' ) !== false )
		return __( 'Database and Files', 'hmbkp' );

	if ( strpos( $type, 'file' ) !== false )
		return __( 'Files', 'hmbkp' );

	if ( strpos( $type, 'database' ) !== false )
		return __( 'Database', 'hmbkp' );

	if ( ! is_null( $schedule ) )
		return hmbkp_human_get_type( $schedule->get_type() );

	return __( 'Legacy', 'hmbkp' );

}


/**
 * Display the row of actions for a schedule
 *
 * @access public
 * @param HMBKP_Scheduled_Backup $schedule
 * @return void
 */
function hmbkp_schedule_actions( HMBKP_Scheduled_Backup $schedule ) {

	if ( $status = $schedule->get_status() ) { ?>

		<span class="hmbkp-status"><?php echo $status; ?> <a href="<?php echo add_query_arg( array( 'action' => 'hmbkp_cancel', 'hmbkp_schedule_id' => $schedule->get_id() ), HMBKP_ADMIN_URL ); ?>"><?php _e( 'cancel', 'hmbkp' ); ?></a></span>

	<?php } else { ?>

	<div class="hmbkp-schedule-actions row-actions">

		<a class="fancybox" href="<?php echo add_query_arg( array( 'action' => 'hmbkp_edit_schedule_load', 'hmbkp_schedule_id' => $schedule->get_id() ), admin_url( 'admin-ajax.php' ) ); ?>"><?php _e( 'Settings', 'hmbkp' ); ?></a> |

	<?php if ( $schedule->get_type() !== 'database' ) { ?>
		<a class="fancybox" href="<?php echo add_query_arg( array( 'action' => 'hmbkp_edit_schedule_excludes_load', 'hmbkp_schedule_id' => $schedule->get_id() ), admin_url( 'admin-ajax.php' ) ); ?>"><?php _e( 'Excludes', 'hmbkp' ); ?></a>  |
	<?php } ?>

		<a class="hmbkp-run" href="<?php echo add_query_arg( array( 'action' => 'hmbkp_run_schedule', 'hmbkp_schedule_id' => $schedule->get_id() ), admin_url( 'admin-ajax.php' ) ); ?>"><?php _e( 'Run now', 'hmbkp' ); ?></a>  |

		<a class="delete-action" href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'hmbkp_delete_schedule', 'hmbkp_schedule_id' => $schedule->get_id() ), HMBKP_ADMIN_URL ), 'hmbkp-delete_schedule' ); ?>"><?php _e( 'Delete', 'hmbkp' ); ?></a>

	</div>

	<?php }

}

/**
 * Load the backup errors file
 *
 * @return string
 */
function hmbkp_backup_errors() {

	if ( ! file_exists( hmbkp_path() . '/.backup_errors' ) )
		return '';

	return file_get_contents( hmbkp_path() . '/.backup_errors' );

}

/**
 * Load the backup warnings file
 *
 * @return string
 */
function hmbkp_backup_warnings() {

	if ( ! file_exists( hmbkp_path() . '/.backup_warnings' ) )
		return '';

	return file_get_contents( hmbkp_path() . '/.backup_warnings' );

}