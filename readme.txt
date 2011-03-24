=== BackUp WordPress ===
Contributors: humanmade, willmot
Tags: backup
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 0.4.5

Simple automated backups of your WordPress powered website.

BackUp WordPress will back up your entire site including your database and all your files once every day.

== Installation ==

1. Install Back Up WordPress either via the WordPress.org plugin directory, or by uploading the files to your server
2. Activate the plugin
3. Sit back and relax safe in the knowledge that your complete site will be backed up every day at 11:00

The plugin will try to use the `mysqldump` and `zip` commands via shell if they are available, using these will greatly improve the time it takes to back up your site. You can point the plugin in the right direction by defining `HMBKP_ZIP_PATH` and `HMBKP_MYSQLDUMP_PATH` in your `wp-config.php`

== Support ==

Contact hello@humanmade.co.uk for help and support

== Screenshots ==

1. Simple Automated Backups

== Changelog ==

#### 1.0

1.0 represents a total rewrite & rethink of the BackUpWordPress plugin with a focus on making it "Just Work". The management and development of the plugin has been taken over by [humanmade](http://humanmade.co.uk) the chaps behind [WP Remote](https://wpremote.com)

#### Previous

Version 0.4.5 and previous were developed by [wpdprx](http://profiles.wordpress.org/users/wpdprx/)