<?php

/*
Plugin Name: BackUpWordPress
Plugin URI: http://humanmade.co.uk/
Description: Simple automated backups of your WordPress powered website.
Author: Human Made Limited
Version: 1.0
Author URI: http://humanmade.co.uk/
*/

/*  Copyright 2011  Human Made Limited  (email : hello@humanmade.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// TODO use wp_filesystem
// TODO try to use zipArchive before pclzip
// TODO get rid of schedules

define( 'HMBKP_PLUGIN_PATH', WP_PLUGIN_DIR .'/humanmade.backup' );
define( 'HMBKP_PLUGIN_URL', WP_PLUGIN_URL .'/humanmade.backup' );

// Load the admin actions file
function hmbkp_actions() {
	
	hmbkp_update();
	
	require_once( HMBKP_PLUGIN_PATH . '/admin.actions.php' );

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'humanmade_backups' ) :
		wp_enqueue_script( 'hmbkp', HMBKP_PLUGIN_URL . '/assets/hmbkp.js' );
		wp_enqueue_style( 'hmbkp', HMBKP_PLUGIN_URL . '/assets/hmbkp.css' );
	endif;

}
add_action( 'admin_init', 'hmbkp_actions' );

// Load the admin menu
require_once( HMBKP_PLUGIN_PATH . '/admin.menus.php' );

// Load the core functions
require_once( HMBKP_PLUGIN_PATH . '/functions/core.functions.php' );
require_once( HMBKP_PLUGIN_PATH . '/functions/interface.functions.php' );
require_once( HMBKP_PLUGIN_PATH . '/functions/settings.functions.php' );
require_once( HMBKP_PLUGIN_PATH . '/functions/backup.log.functions.php' );
require_once( HMBKP_PLUGIN_PATH . '/functions/backup.functions.php' );
require_once( HMBKP_PLUGIN_PATH . '/functions/backup.mysql.functions.php' );
require_once( HMBKP_PLUGIN_PATH . '/functions/backup.files.functions.php' );
require_once( HMBKP_PLUGIN_PATH . '/functions/backup.mysql.fallback.functions.php' );
require_once( HMBKP_PLUGIN_PATH . '/functions/backup.files.fallback.functions.php' );

// Plugin activation and deactivation
add_action( 'activate_humanmade.backup/plugin.php', 'hmbkp_activate' );
add_action( 'deactivate_humanmade.backup/plugin.php', 'hmbkp_deactivate' );

// Add more cron schedules
add_filter( 'cron_schedules', 'hmbkp_more_reccurences' );

// Cron hook for backups
add_action( 'hmbkp_schedule_backup_hook', 'hmbkp_schedule_backup' );

// Make sure backups dir exists and is writable
if ( !is_dir( hmbkp_path() ) ) :

    function hmbkp_path_exists_warning() {
	    $php_user = exec( 'whoami' );
		$php_group = reset( explode( ' ', exec( 'groups' ) ) );
    	echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress has detected a problem.' ) . '</strong> ' . sprintf( __( 'The backups directory can\'t be created because your %s directory isn\'t writable, run %s or %s or create the folder yourself.' ), '<code>wp-content</code>', '<code>chown ' . $php_user . ':' . $php_group . ' ' . WP_CONTENT_DIR . '</code>', '<code>chmod 777 ' . WP_CONTENT_DIR . '</code>' ) . '</p></div>';
    }
    add_action( 'admin_notices', 'hmbkp_path_exists_warning' );

endif;

if ( is_dir( hmbkp_path() ) && !is_writable( hmbkp_path() ) ) :

    function hmbkp_writable_path_warning() {
		$php_user = exec( 'whoami' );
		$php_group = reset( explode( ' ', exec( 'groups' ) ) );
    	echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress has detected a problem.' ) . '</strong> ' . sprintf( __( 'Your backups directory isn\'t writable. run %s or %s or set the permissions yourself.' ), '<code>chown -R ' . $php_user . ':' . $php_group . ' ' . hmbkp_path() . '</code>', '<code>chmod -R 777 ' . hmbkp_path() . '</code>' ) . '</p></div>';
    }
    add_action( 'admin_notices', 'hmbkp_writable_path_warning' );

endif; ?>