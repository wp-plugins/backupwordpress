<?php $schedules = get_option( 'hmbkp_schedules' ); ?>

<div class="wrap<?php if ( hmbkp_is_in_progress() ) { ?> hmbkp_running<?php } ?>">

	<h2>

		<?php _e( 'Manage Backups', 'hmbkp' ); ?>

<?php if ( hmbkp_is_in_progress() ) : ?>
		<a class="button add-new-h2" <?php disabled( true ); ?>><img src="<?php echo site_url( 'wp-admin/images/wpspin_light.gif' ); ?>" width="16" height="16" /><?php _e( 'Backup Running', 'hmbkp' ); ?></a>

<?php elseif ( !is_writable( hmbkp_path() ) || !is_dir( hmbkp_path() ) ) : ?>
		<a class="button add-new-h2" <?php disabled( true ); ?>><?php _e( 'Backup Now', 'hmbkp' ); ?></a>

<?php else : ?>
		<a class="button add-new-h2" href="tools.php?page=<?php echo $_GET['page']; ?>&amp;action=hmbkp_backup_now"><?php _e( 'Backup Now', 'hmbkp' ); ?></a>

<?php endif; ?>

	</h2>

<?php if ( is_dir( hmbkp_path() ) && is_writable( hmbkp_path() ) ) : ?>

	<p>
		<?php printf( __( 'Your %s &amp; %s will be automatically backed up every day at %s to %s.', 'hmbkp' ), '<code>' . __( 'database', 'hmbkp' ) . '</code>', '<code>' . __( 'files', 'hmbkp' ) . '</code>', '<code>' . date( 'H:i', wp_next_scheduled( 'hmbkp_schedule_backup_hook', $schedules['default'] ) ) . '</code>', '<code>' . trailingslashit( hmbkp_path() ) . '</code>' ); ?>	
		<span class="hmbkp_estimated-size"><?php printf( __( 'Each backup will be approximately %s.', 'hmbkp' ), '<code>Calculating Size...</code>' ); ?></span>
	</p>

	<?php $backup_archives = hmbkp_get_backups();
	if ( count( $backup_archives ) ) : ?>

	<h4><?php _e( 'Completed Backups', 'hmbkp' ); ?></h4>

	<table class="widefat" id="hmbkp_manage_backups_table">
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Date &amp; Time', 'hmbkp' ); ?></th>
				<th scope="col"><?php _e( 'Size', 'hmbkp' ); ?></th>
				<th scope="col"><?php _e( 'Actions', 'hmbkp' ); ?></th>
			</tr>
	</thead>

	<tbody id="the-list">

		<?php foreach ( (array) $backup_archives as $file ) :

		    if ( !file_exists( $file['file'] ) )
		    	continue;

		    hmbkp_get_backup_row( $file );

		endforeach; ?>

		</tbody>
	</table>

	<?php endif; ?>

	<?php if ( count( $backup_archives ) >= get_option( 'hmbkp_max_backups' ) ) : ?>

	<p class="howto"><?php printf( _n( '* Only the latest backup is saved.', '* Only the latest %d backups are saved.', (int) get_option( 'hmbkp_max_backups' ), 'hmbkp' ) ); ?></p>

	<?php endif; ?>

	<h4><?php _e( 'Compatibility', 'hmbkp' ); ?></h4>

<?php if ( !hmbkp_zip_path() || !hmbkp_mysqldump_path() ) : ?>
	<p><?php _e( 'You can increase the speed and reliability of your backups by resolving the items below. Your backups will still work fine if you don\'t.', 'hmnkp' ); ?></p>
<?php endif; ?>

<?php if ( !hmbkp_zip_path() ) : ?>

	<p>&#10007; <?php printf( __( 'We couldn\'t find the %s command on your server.', 'hmbkp' ), '<code>zip</code>' ); ?></p>

	<p><?php printf( __( 'You can fix this by adding %s to your %s file. run %s on your server to find the path or ask your server administrator.', 'hmbkp' ), '<code>' . define( 'HMBKP_ZIP_PATH', __( 'path to the zip command', 'hmbkp' ) ) . '</code>', '<code>wp-config.php</code>', '<code>which zip</code>' ); ?></p>

<?php else : ?>
	<p>&#10003; <?php printf( __( 'Your files are being backed up using the %s command.', 'hmbkp' ), '<code>' . hmbkp_zip_path() . '</code>' ); ?></p>

<?php endif; ?>

<?php if ( !hmbkp_mysqldump_path() ) : ?>
	<p>&#10007; <?php printf( __( 'We couldn\'t find the %s command on your server.', 'hmbkp' ), '<code>mysqldump</code>' ); ?></p>
	<p><?php printf( __( 'You can fix this by adding %s to your %s file. run %s on your server to find the path or ask your server administrator.', 'hmbkp' ), '<code>' . define( 'HMBKP_MYSQLDUMP_PATH', __( 'path to the mysqldump command' ) ) . '</code>', '<code>wp-config.php</code>', '<code>which mysqldump</code>' ); ?></p>
<?php else : ?>
    <p>&#10003; <?php printf( __( 'Your database is being backed up using the %s command.', 'hmbkp' ), '<code>' . hmbkp_mysqldump_path() . '</code>' ); ?></p>
<?php endif; ?>

<?php if ( defined( 'HMBKP_PATH' ) && HMBKP_PATH ) :
	if ( !is_dir( HMBKP_PATH ) || !is_writable( HMBKP_PATH ) ) : ?>
		<p>&#10007; <code><?php echo HMBKP_PATH; ?></code><?php printf( __( 'doesn\'t exist or isn\'t writable. your backups will be saved to %s.', 'hmbkp' ), '<code>' . hmbkp_path() . '</code>' ); ?></p>

	<?php else : ?>
		<p>&#10003; <?php printf( __( 'Your backups are being saved to %s.', 'hmbkp' ), '<code>' . hmbkp_path() . '</code>' ); ?></p>

	<?php endif; ?>
<?php endif ; ?>

<?php else : ?>

	<p><strong><?php _e( 'You need to fix the issues detailed above before BackUpWordPress can start.', 'hmbkp' ); ?></strong></p>
<?php endif; ?>

	<p class="howto"><?php printf( __( 'If you need help getting things working you are more than welcome to email us at %s and we\'ll do what we can.', 'hmbkp' ), '<a href="mailto:support@humanmade.co.uk">support@humanmade.co.uk</a>' ); ?></p>

</div>