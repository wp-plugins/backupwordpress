<?php

/**
 * Delete the backup and then redirect
 * back to the backups page
 */
function hmbkp_request_delete_backup() {

	if ( !isset( $_GET['hmbkp_delete'] ) || empty( $_GET['hmbkp_delete'] ) )
		return false;

	hmbkp_delete_backup( $_GET['hmbkp_delete'] );

	wp_redirect( remove_query_arg( 'hmbkp_delete' ) );
	exit;

}
add_action( 'load-tools_page_' . HMBKP_PLUGIN_SLUG, 'hmbkp_request_delete_backup' );

/**
 * Schedule a one time backup and then
 * redirect back to the backups page
 */
function hmbkp_request_do_backup() {

	// Are we sure
	if ( !isset( $_GET['action'] ) || $_GET['action'] !== 'hmbkp_backup_now' || hmbkp_is_in_progress() || !is_writable( hmbkp_path() ) || !is_dir( hmbkp_path() ) )
		return false;

		$options = array(
			'preset' => 'full backup',
			'info' => __( 'Single Backup', 'hmbkp' ),
			'created' => time()
		);

	wp_schedule_single_event( time(), 'hmbkp_schedule_backup_hook', $options );

	spawn_cron();

	wp_redirect( remove_query_arg( 'action' ) );
	exit;

}
add_action( 'load-tools_page_' . HMBKP_PLUGIN_SLUG, 'hmbkp_request_do_backup' );

/**
 * Send the download file to the browser and
 * then redirect back to the backups page
 */
function hmbkp_request_download_backup() {

	if ( !isset( $_GET['hmbkp_download'] ) || empty( $_GET['hmbkp_download'] ) )
		return false;

	hmbkp_send_file( base64_decode( $_GET['hmbkp_download'] ) );

}
add_action( 'load-tools_page_' . HMBKP_PLUGIN_SLUG, 'hmbkp_request_download_backup' );

function hmbkp_ajax_is_backup_in_progress() {
	echo (int) hmbkp_is_in_progress();
	exit;
}
add_action( 'wp_ajax_hmbkp_is_in_progress', 'hmbkp_ajax_is_backup_in_progress' );

function hmbkp_ajax_calculate_backup_size() {
	echo hmbkp_calculate();
	exit;
}
add_action( 'wp_ajax_hmbkp_calculate', 'hmbkp_ajax_calculate_backup_size' );