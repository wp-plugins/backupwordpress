<?php
/*
Plugin Name: BackUpWordPress
Plugin URI: http://wordpress.designpraxis.at
Description: Manage <a href="admin.php?page=backupwordpress/backupwordpress.php">WordPress Backups</a>. Beta Release. Please help testing and give me feedback under the comments section of <a href="http://wordpress.designpraxis.at/plugins/backupwordpress/">the Plugin page</a>. Backup DB, Files & Folders, use .tar.gz, .zip, Exclude List, etc.
Author: Roland Rust
Version: 0.2.2
Author URI: http://wordpress.designpraxis.at
*/

/*
Notes:
backupwordpress\Archive\Writer\Tar.php has been debugged around line 80 to handle long filenames according to http://pear.php.net/bugs/bug.php?id=10144&edit=3

Whishlist:
	- custom schedules
*/

/*

Changelog:
	
Changes in 0.2.2:
	- Manage Backups now display the type, either scheduled or manual for Advanced as well as EasyMode
	- logfile prints out WordPress and BackUpWordPress version for easier posting at http://wpforum.designpraxis.at/
	- BackUpWordPress displays "Your backup is being processed" instead of the actions links on the Manage Backups screen as long as archiving is not finished
	
Changes in 0.2.1:
	- old Logfiles are deleted. 10 times the amount of the configured amount of backups to keep is kept.
	- feature: backups are done in kind of a staggered process:
	 	if BackUpWordPress runs into a server side time-out, BackUpWordPress tries to trigger an single scheduled event for finishing the task. Corresponding dialoques appear on the *Manage Backups* - screen. 
	
Changes in 0.1.4:
	- @set_time_limit(0) in functions.php line 277 supresses the 'Cannot set time limit in safe mode' warning
	- dialoques streamlined: e.g. when you click "delete" on a backup archive, you just need to hit enter to delete it
	
Changes in 0.1.3:
	- ajax problems fixed
	- schedules last run timestamp fixed

Changes in 0.1.2:
	- bug fixed: Backup-Now doesn't call Sajax
	- bkpwp_delete_old() refactored
	- bug fixed: table data is not dumped

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
$GLOBALS['bkpwp_plugin_path'] = ABSPATH."wp-content/plugins/backupwordpress/";
$GLOBALS['bkpwp_version'] = "0.2.2";

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
add_action('activate_backupwordpress/backupwordpress.php', 'bkpwp_activate');
add_action('deactivate_backupwordpress/backupwordpress.php', 'bkpwp_exit');
	
// set up ajax stuff on init, to prevent header oputput
add_action('init', 'bkpwp_download_files');
add_action('init', 'bkpwp_setup');
add_action('init', 'bkpwp_sajax_do');
add_action('init', 'bkpwp_proceed_unfinished');

// cron jobs with wordpress' pseude-cron: add special reccurences
add_filter('cron_schedules', 'bkpwp_more_reccurences');

add_action('bkpwp_schedule_bkpwp_hook','bkpwp_schedule_bkpwp');
add_action('bkpwp_finish_bkpwp_hook','bkpwp_finish_bkpwp');

if (eregi("backupwordpress",$_REQUEST['page']) || eregi("bkpwp",$_REQUEST['page'])) {
add_action('admin_head', 'bkpwp_sajax_js');
}
if (eregi("backupwordpress",$_REQUEST['page']) || eregi("bkpwp",$_REQUEST['page']) || $_SERVER['REQUEST_URI'] == "/wp-admin/index.php") {
add_action('admin_head', 'bkpwp_load_css_and_js');
}

add_action('admin_menu', 'bkpwp_add_menu');
add_action('activity_box_end', 'bkpwp_latest_activity',0);
?>
