<?php

/**
 * Displays a row in the manage backups table
 *
 * @param string                 $file
 * @param HMBKP_Scheduled_Backup $schedule
 */
function hmbkp_get_backup_row( $file, HMBKP_Scheduled_Backup $schedule ) {

	$encoded_file = urlencode( base64_encode( $file ) );
	$offset       = get_option( 'gmt_offset' ) * 3600;

	?>

	<tr class="hmbkp_manage_backups_row">

		<th scope="row">
			<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), @filemtime( $file ) + $offset ) ); ?>
		</th>

		<td class="code">
			<?php echo esc_html( size_format( @filesize( $file ) ) ); ?>
		</td>

		<td><?php echo esc_html( hmbkp_human_get_type( $file, $schedule ) ); ?></td>

		<td>

			<?php if (  hmbkp_is_path_accessible( hmbkp_path() )  ) : ?>
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'hmbkp_backup_archive' => $encoded_file, 'hmbkp_schedule_id' => $schedule->get_id(), 'action' => 'hmbkp_request_download_backup' ), admin_url( 'admin-post.php' ) ), 'hmbkp_download_backup', 'hmbkp_download_backup_nonce' ) ); ?>" class="download-action"><?php _e( 'Download', 'hmbkp' ); ?></a> |
			<?php endif; ?>

			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'hmbkp_backup_archive' => $encoded_file, 'hmbkp_schedule_id' => $schedule->get_id(), 'action' => 'hmbkp_request_delete_backup' ), admin_url( 'admin-post.php' ) ), 'hmbkp_delete_backup', 'hmbkp_delete_backup_nonce' ) ); ?>" class="delete-action"><?php _e( 'Delete', 'hmbkp' ); ?></a>

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

	$notices = HMBKP_Notices::get_instance()->get_notices();
	
	if ( empty( $notices ) ) {
		return;
	}

	ob_start(); ?>

	<?php if ( ! empty( $notices['backup_errors'] ) ) : ?>

		<div id="hmbkp-warning" class="error fade">
			<p>
				<strong><?php _e( 'BackUpWordPress detected issues with your last backup.', 'hmbkp' ); ?></strong>
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'hmbkp_dismiss_error' ), admin_url( 'admin-post.php' ) ), 'hmbkp_dismiss_error', 'hmbkp_dismiss_error_nonce' ) ); ?>" style="float: right;" class="button">
					<?php _e( 'Dismiss', 'hmbkp' ); ?>
				</a>
			</p>

			<ul>
				<?php foreach ( $notices['backup_errors'] as $notice ) : ?>
					<li><?php echo esc_html( $notice ); ?></li>
				<?php endforeach; ?>
			</ul>

		</div>
	<?php endif; ?>

	<?php if ( ! empty( $notices['server_config'] ) ) : ?>

		<div id="hmbkp-warning" class="error fade">
			<p>
				<strong><?php _e( 'BackUpWordPress has detected a problem.', 'hmbkp' ); ?></strong>
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'hmbkp_dismiss_error' ), admin_url( 'admin-post.php' ) ), 'hmbkp_dismiss_error', 'hmbkp_dismiss_error_nonce' ) ); ?>" style="float: right;" class="button">
					<?php _e( 'Dismiss', 'hmbkp' ); ?>
				</a>
			</p>
				<ul>
					<?php foreach ( $notices['server_config'] as $notice ) : ?>
						<li><?php echo $notice; ?></li>
					<?php endforeach; ?>
				</ul>
		</div>

	<?php endif; ?>

	<?php echo ob_get_clean();

}
add_action( 'admin_notices', 'hmbkp_admin_notices' );

function hmbkp_set_server_config_notices() {

	$notices = HMBKP_Notices::get_instance();

	$messages = array();

	$php_user  = exec( 'whoami' );
	$groups = explode( ' ', exec( 'groups' ) );
	$php_group = reset( $groups );

	if ( ! is_dir( hmbkp_path() ) ) {

		$messages[] = sprintf( __( 'The backups directory can\'t be created because your %1$s directory isn\'t writable, run %2$s or %3$s or create the folder yourself.', 'hmbkp' ), '<code>wp-content</code>', '<code>chown ' . esc_html( $php_user ) . ':' . esc_html( $php_group ) . ' ' . esc_html( dirname( hmbkp_path() ) ) . '</code>', '<code>chmod 777 ' . esc_html( dirname( hmbkp_path() ) ) . '</code>' );

	}

	if ( is_dir( hmbkp_path() ) && ! wp_is_writable( hmbkp_path() ) ) {

		$messages[] = sprintf( __( 'Your backups directory isn\'t writable, run %1$s or %2$s or set the permissions yourself.', 'hmbkp' ), '<code>chown -R ' . esc_html( $php_user ) . ':' . esc_html( $php_group ) . ' ' . esc_html( hmbkp_path() ) . '</code>', '<code>chmod -R 777 ' . esc_html( hmbkp_path() ) . '</code>' );

	}

	if ( HM_Backup::is_safe_mode_active() ) {
		$messages[] = sprintf( __( '%1$s is running in %2$s, please contact your host and ask them to disable it. BackUpWordPress may not work correctly whilst %3$s is on.', 'hmbkp' ), '<code>PHP</code>', sprintf( '<a href="%1$s">%2$s</a>', __( 'http://php.net/manual/en/features.safe-mode.php', 'hmbkp' ), __( 'Safe Mode', 'hmbkp' ) ), '<code>' . __( 'Safe Mode', 'hmbkp' ) . '</code>' );
	}

	if ( defined( 'HMBKP_PATH' ) && HMBKP_PATH && ! is_dir( HMBKP_PATH ) ) {
		$messages[] = sprintf( __( 'Your custom backups directory %1$s doesn\'t exist and can\'t be created, your backups will be saved to %2$s instead.', 'hmbkp' ), '<code>' . esc_html( HMBKP_PATH ) . '</code>', '<code>' . esc_html( hmbkp_path() ) . '</code>' );
	}

	if ( defined( 'HMBKP_PATH' ) && HMBKP_PATH && is_dir( HMBKP_PATH ) && ! wp_is_writable( HMBKP_PATH ) ) {
		$messages[] = sprintf( __( 'Your custom backups directory %1$s isn\'t writable, new backups will be saved to %2$s instead.', 'hmbkp' ), '<code>' . esc_html( HMBKP_PATH ) . '</code>', '<code>' . esc_html( hmbkp_path() ) . '</code>' );
	}

	$test_backup = new HMBKP_Scheduled_Backup( 'test_backup' );

	if ( ! is_readable( $test_backup->get_root() ) ) {
		$messages[] = sprintf( __( 'Your backup root path %s isn\'t readable.', 'hmbkp' ), '<code>' . $test_backup->get_root() . '</code>' );
	}

	if ( count( $messages ) > 0 ) {
		$notices->set_notices( 'server_config', $messages );
	}

}
add_action( 'admin_init', 'hmbkp_set_server_config_notices' );

/**
 * Hook in an change the plugin description when BackUpWordPress is activated
 *
 * @param array $plugins
 * @return array $plugins
 */
function hmbkp_plugin_row( $plugins ) {

	if ( isset( $plugins[HMBKP_PLUGIN_SLUG . '/backupwordpress.php'] ) )
		$plugins[HMBKP_PLUGIN_SLUG . '/backupwordpress.php']['Description'] = str_replace( 'Once activated you\'ll find me under <strong>Tools &rarr; Backups</strong>', 'Find me under <strong><a href="' . esc_url( hmbkp_get_settings_url() ) . '">Tools &rarr; Backups</a></strong>', $plugins[HMBKP_PLUGIN_SLUG . '/backupwordpress.php']['Description'] );

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

	foreach ( (array) json_decode( hmbkp_backup_errors() ) as $key => $errors ) {
		foreach ( $errors as $error ) {
			$message .= '<p><strong>' . esc_html( $key ) . '</strong>: <code>' . implode( ':', array_map( 'esc_html', (array) $error ) ) . '</code></p>';
		}
	}

	return $message;

}

/**
 * Get the human readable backup type in.
 *
 * @access public
 * @param string                 $type
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
function hmbkp_schedule_status( HMBKP_Scheduled_Backup $schedule, $echo = true ) {

	ob_start(); ?>

	<span class="hmbkp-status"<?php if ( $schedule->get_status() ) { ?> title="<?php printf( __( 'Started %s ago', 'hmbkp' ), human_time_diff( $schedule->get_schedule_running_start_time() ) ); ?>"<?php } ?>>
		<?php echo $schedule->get_status() ? wp_kses_data( $schedule->get_status() ) : __( 'Starting Backup', 'hmbkp' ); ?>
		<a href="<?php echo hmbkp_admin_action_url( 'request_cancel_backup', array( 'hmbkp_schedule_id' => $schedule->get_id() ) ); ?>"><?php _e( 'cancel', 'hmbkp' ); ?></a>
	</span>

	<?php $output = ob_get_clean();

	if ( ! $echo ) {
		return $output;
	}

	echo $output;

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

function hmbkp_backups_number( $schedule, $zero = false, $one = false, $more = false ) {

	$number = count( $schedule->get_backups() );

	if ( $number > 1 )
		$output = str_replace( '%', number_format_i18n( $number ), ( false === $more ) ? __( '% Backups Completed', 'hmbkp' ) : $more );
	elseif ( 0 === $number )
		$output = ( false === $zero ) ? __( 'No Backups Completed', 'hmbkp' ) : $zero;
	else // must be one
		$output = ( false === $one ) ? __( '1 Backup Completed', 'hmbkp' ) : $one;

	echo apply_filters( 'hmbkp_backups_number', $output, $number );
}

function hmbkp_translated_schedule_title( $slug, $title ) {

	$titles = array(
		'complete-hourly'      => esc_html__( 'Complete Hourly', 'hmbkp' ),
		'file-hourly'          => esc_html__( 'File Hourly', 'hmbkp' ),
		'database-hourly'      => esc_html__( 'Database Hourly', 'hmbkp' ),
		'complete-twicedaily'  => esc_html__( 'Complete Twicedaily', 'hmbkp' ),
		'file-twicedaily'      => esc_html__( 'File Twicedaily', 'hmbkp' ),
		'database-twicedaily'  => esc_html__( 'Database Twicedaily', 'hmbkp' ),
		'complete-daily'       => esc_html__( 'Complete Daily', 'hmbkp' ),
		'file-daily'           => esc_html__( 'File Daily', 'hmbkp' ),
		'database-daily'       => esc_html__( 'Database Daily', 'hmbkp' ),
		'complete-weekly'      => esc_html__( 'Complete Weekly', 'hmbkp' ),
		'file-weekly'          => esc_html__( 'File Weekly', 'hmbkp' ),
		'database-weekly'      => esc_html__( 'Database Weekly', 'hmbkp' ),
		'complete-fortnightly' => esc_html__( 'Complete Biweekly', 'hmbkp' ),
		'file-fortnightly'     => esc_html__( 'File Biweekly', 'hmbkp' ),
		'database-fortnightly' => esc_html__( 'Database Biweekly', 'hmbkp' ),
		'complete-monthly'     => esc_html__( 'Complete Monthly', 'hmbkp' ),
		'file-monthly'         => esc_html__( 'File Monthly', 'hmbkp' ),
		'database-monthly'     => esc_html__( 'Database Monthly', 'hmbkp' ),
		'complete-manually'    => esc_html__( 'Complete Manually', 'hmbkp' ),
		'file-manually'        => esc_html__( 'File Manually', 'hmbkp' ),
		'database-manually'    => esc_html__( 'Database Manually', 'hmbkp' )
	);

	if ( isset( $titles[ $slug ] ) ) {
		return $titles[ $slug ];
	}

	return $title;

}

function hmbkp_get_settings_url() {

	if ( is_multisite() ) {
		$url = network_admin_url( 'settings.php?page=' . HMBKP_PLUGIN_SLUG );
	} else {
		$url = admin_url( 'tools.php?page=' . HMBKP_PLUGIN_SLUG );
	}

	HMBKP_schedules::get_instance()->refresh_schedules();

	if ( ! empty( $_REQUEST['hmbkp_schedule_id'] ) && HMBKP_schedules::get_instance()->get_schedule( sanitize_text_field( $_REQUEST['hmbkp_schedule_id'] ) ) ) {
		$url = add_query_arg( 'hmbkp_schedule_id', sanitize_text_field( $_REQUEST['hmbkp_schedule_id'] ), $url );
	}

	return $url;

}

/**
 * Add an error message to the array of messages.
 *
 * @param $error_message
 */
function hmbkp_add_settings_error( $error_message ){

	$hmbkp_settings_errors = get_transient( 'hmbkp_settings_errors' );

	// If it doesnt exist, create.
	if ( ! $hmbkp_settings_errors ) {
		set_transient( 'hmbkp_settings_errors', (array) $error_message );
	} else {
		set_transient( 'hmbkp_settings_errors', array_unique( array_merge( $hmbkp_settings_errors, (array) $error_message ) ) );
	}

}

/**
 * Fetch the form submission errors for display.
 *
 * @return mixed
 */
function hmbkp_get_settings_errors() {

	return get_transient( 'hmbkp_settings_errors' );
}

/**
 * Clear all error messages.
 *
 * @return bool
 */
function hmbkp_clear_settings_errors(){
	return delete_transient( 'hmbkp_settings_errors' );
}
