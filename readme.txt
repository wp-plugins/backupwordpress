=== BackUpWordPress ===
Contributors: willmot, humanmade
Tags: back up, backup, backups
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 1.1

Simple automated backups of your WordPress powered website.

BackUpWordPress will back up your entire site including your database and all your files once every day.

== Installation ==

1. Install BackUpWordPress either via the WordPress.org plugin directory, or by uploading the files to your server
2. Activate the plugin
3. Sit back and relax safe in the knowledge that your complete site will be backed up every day at 11:00

The plugin will try to use the `mysqldump` and `zip` commands via shell if they are available, using these will greatly improve the time it takes to back up your site. You can point the plugin in the right direction by defining `HMBKP_ZIP_PATH` and `HMBKP_MYSQLDUMP_PATH` in your `wp-config.php`.

== Frequently Asked Questions ==

Contact support@humanmade.co.uk for help/support.

== Screenshots ==

1. Simple Automated Backups

== Changelog ==

#### 1.1

* Remove the logging facility as it provided little benefit and complicated the code, your existing logs will be deleted on update.
* Expose the various `Constants` that can be defined to change advanced settings.
* Added the ability to disable the automatic backups completely `define( 'HMBKP_DISABLE_AUTOMATIC_BACKUP', true );`.
* Added the ability to switch to file only or database only backups `define( 'HMBKP_FILES_ONLY', true );` Or `define( 'HMBKP_DATABASE_ONLY', true );`.
* Added the ability to define how many old backups should be kept `define( 'HMBKP_MAX_BACKUPS', 20 );`
* Added the ability to define the time that the daily backup should run `define( 'HMBKP_DAILY_SCHEDULE_TIME', '16:30' );`
* Tweaks to the backups page layout.
* General bug fixes and improvements.

#### 1.0.5

* Don't ajax load estimated backup size if it's already been calculated.
* Fix time in backup complete log message.
* Don't mark backup as running until cron has been called, will fix issues with backup showing as running even if cron never fired.
* Show number of backups saved message.
* Add a link to the backups page to the plugin action links.

#### 1.0.4

Don't throw PHP Warnings when `shell_exec` is disabled

#### 1.0.3

Minor bug fix release.

* Suppress `filesize()` warnings when calculating backup size.
* Plugin should now work when symlinked.
* Remove all options on deactivate, you should now be able to deactivate then activate to fix issues with settings etc. becoming corrupt.
* Call setup_defaults for users who update from backupwordpress 0.4.5 so they get new settings.
* Don't ajax ping running backup status quite so often.

#### 1.0.1 & 1.0.2

Fix some silly 1.0 bugs

#### 1.0

1.0 represents a total rewrite & rethink of the BackUpWordPress plugin with a focus on making it "Just Work". The management and development of the plugin has been taken over by [humanmade](http://humanmade.co.uk) the chaps behind [WP Remote](https://wpremote.com)

#### Previous

Version 0.4.5 and previous were developed by [wpdprx](http://profiles.wordpress.org/users/wpdprx/)