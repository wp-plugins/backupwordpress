<?php
/*
Plugin Name: BackUpWordPress
Plugin URI: http://wordpress.designpraxis.at
Description: Manage <a href="admin.php?page=BackUpWP/bkpwp.php">WordPress Backups</a>. Alpha Release. Please help testing and give me feedback under . Backup DB, Files & Folders, use .tar.gz, .zip, Exclude List, etc.
Author: Roland Rust
Version: 0.1beta
Author URI: http://wordpress.designpraxis.at
*/

/*
Notes:
BackUpWP\Archive\Writer\Tar.php has been debugged around line 80 to handle long filenames according to http://pear.php.net/bugs/bug.php?id=10144&edit=3
*/

/*  Copyright 2007  Roland Rust  (email : wordpress@designpraxis.at)

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
//return;
$GLOBALS['bkpwp_plugin_path'] = ABSPATH."wp-content/plugins/BackUpWP/";

// get the functions
require_once($GLOBALS['bkpwp_plugin_path']."functions.php");
// require_once the required PEAR::FILE_ARCHIVE package for files backup
require_once $GLOBALS['bkpwp_plugin_path']."Archive.php";
require_once $GLOBALS['bkpwp_plugin_path']."Type.php";
// BackUpWordPress classes
require_once($GLOBALS['bkpwp_plugin_path']."bkpwp-classes/interface.php");
require_once($GLOBALS['bkpwp_plugin_path']."bkpwp-classes/options.php");
require_once($GLOBALS['bkpwp_plugin_path']."bkpwp-classes/manage_backups.php");
require_once($GLOBALS['bkpwp_plugin_path']."bkpwp-classes/schedule.php");
require_once($GLOBALS['bkpwp_plugin_path']."functions-interface.php");

// Plugin activation and deactivation e.g.: set 'manage bkpwp' capabilities to admin
add_action('activate_BackUpWP/bkpwp.php', 'bkpwp_activate');
add_action('deactivate_BackUpWP/bkpwp.php', 'bkpwp_exit');
	
// set up ajax stuff on init, to prevent header oputput
add_action('init', 'bkpwp_download_files');
add_action('init', 'bkpwp_setup');
add_action('init', 'bkpwp_sajax_do');

// cron jobs with wordpress' pseude-cron: add special reccurences
add_filter('cron_schedules', 'bkpwp_more_reccurences');

add_action('bkpwp_schedule_bkpwp_hook','bkpwp_schedule_bkpwp');

if (eregi("bkpwp",$_REQUEST['page'])) {
add_action('admin_head', 'bkpwp_sajax_js');
}
if (eregi("bkpwp",$_REQUEST['page']) || $_SERVER['REQUEST_URI'] == "/wp-admin/index.php") {
add_action('admin_head', 'bkpwp_load_css_and_js');
}

add_action('admin_menu', 'bkpwp_add_menu');
add_action('activity_box_end', 'bkpwp_latest_activity',0);
?>
