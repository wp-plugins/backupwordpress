<?php
/**
 * hmbkp_get_backup_row function.
 *
 * @param mixed $file
 * @param mixed $alternate
 * @return void
 */
function hmbkp_get_backup_row( $file ) {

	$encode = base64_encode( $file['file'] ); ?>

	<tr id="hmbkp_manage_backups_row_<?php echo $encode; ?>" class="hmbkp_manage_backups_row">

		<th scope="row">
			<?php echo date( get_option('date_format'), filemtime( $file['file'] ) ) . ' ' . date( 'H:i', filemtime($file['file'] ) ); ?>
		</th>

		<td>
			<?php echo hmbkp_size_readable( filesize( $file['file'] ) ); ?>
		</td>

		<td>

			<a href="tools.php?page=humanmade_backups&amp;hmbkp_download=<?php echo $encode; ?>"><?php _e( 'Download', 'hmbkp' ); ?></a> |
			<a href="tools.php?page=humanmade_backups&amp;hmbkp_delete=<?php echo $encode ?>"><?php _e( 'Delete', 'hmbkp' ); ?></a>

		</td>

	</tr>

<?php }