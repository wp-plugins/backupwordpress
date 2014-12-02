
n.n.n / 2014-12-02
==================

  * Bump version
  * Merge pull request #600 from humanmade/issue-600
  * Merge pull request #347 from humanmade/issue-347
  * Add parenthesis to require_once
  * Remove disk space info
  * Merge branch 'master' into issue-347
  * Merge pull request #614 from humanmade/remove-custom-webhook
  * Merge branch 'master' into remove-custom-webhook
  * Merge branch 'master' into issue-600
  * Merge pull request #611 from humanmade/fix_unit_tests
  * Attempt to fix build
  * Merge branch 'master' into fix_unit_tests
  * define WP_TESTS_DIR
  * Make sure current_action fnction is loaded
  * Remove extra slashes and semicolons
  * Remove the custom webhook service
  * Better default WP_TESTS_DIR
  * move trailingslash calls out of the loop for performance
  * Minor code cleanup
  * Merge pull request #599 from humanmade/issue-599
  * Correct path for hm-backup so it's tests are run
  * add composer.lock
  * Use scrutinizer code coverage
  * Merge pull request #609 from waffle-iron/master
  * add waffle.io badge
  * Use up to date commands for coverage
  * Add code rating
  * Don't test 5.2
  * Update readme.md
  * excluded_dirs is deprecated
  * Update scrutinizer config
  * Add composer config and dev dependencies
  * Make PHPUnit generate an XML for coveralls
  * Add scrutinizer config
  * Ignore vendor dir
  * Add a link to Help page
  * Move to activation hook
  * Bump up required WP version
  * Escape all the things
  * Save errors to a notices option
  * Reload page on errors
  * Remove script
  * Remove unneeded class
  * Merge branch 'nice-errors' into issue-600
  * Check PHP version on plugins loaded
  * Check PHP version on activate
  * Merge branch 'master' into issue-599
  * Merge pull request #522 from humanmade/issue-522
  * Merge branch 'master' into nice-errors
  * Update readme
  * Set required version to 5.3.2
  * Update tests
  * Merge branch 'master' into issue-522
  * Merge pull request #567 from humanmade/issue-567
  * Merge branch 'master' into issue-522
  * Merge pull request #559 from humanmade/issue-559
  * Merge branch 'master' into issue-522
  * Merge branch 'master' into nice-errors
  * Allow for a 30 second delta in asserting schedule time
  * Merge pull request #603 from joshk/patch-1
  * Use the new build env on Travis
  * Restrict plugin to be network only
  * Fix admin URL logic
  * Merge pull request #571 from humanmade/codesniffs
  * Formatting
  * Merge branch 'master' into codesniffs
  * Merge branch 'master' into nice-errors
  * Update readme changelog
  * Bump version
  * Ignoe history log
  * Merge branch 'master' into nice-errors
  * remove uneeded images
  * latest hm-backup
  * latest hm-backup
  * Latest hm backup
  * Added known errors and nice messages
  * Fix the support button splitting on 2 lines when too many tabs
  * Spaces
  * Merge branch 'master' into nice-errors
  * Typos in v 3.0 changelog
  * Specify POT filename in grunt task
  * Update POT file
  * Markdown readme
  * Regenerate readme
  * Add plugin headers
  * Fix semicolon
  * Merge branch 'master' of github.com:humanmade/backupwordpress
  * Refactor the recursive filesize scanner
  * correct text domain
  * Merge pull request #556 from humanmade/issue-556
  * Merge pull request #580 from humanmade/issue-580
  * Merge pull request #584 from humanmade/update-backdrop
  * See if this fixes tests
  * Remove unneede statements
  * Use correct action hook
  * Fix display notices
  * Set notices
  * Formatting
  * Merge branch 'master' into nice-errors
  * reload the excludes filelist in the correct place when someone excludes a file
  * Latest backdrop
  * Regenerate minified CSS
  * Regenerate minified JS
  * Fix cancel backup action
  * WordPress Coding Standards
  * Exit early if incompatible version of WordPress
  * Update translations
  * Update changelog
  * Merge pull request #573 from humanmade/design
  * Load minified CSS
  * Formatting
  * Biweekly wording
  * More Yoda conditions
  * Spaces
  * Comma after last array element
  * Yoda conditions
  * Add missing period
  * use nonce_url instead of manually adding the nonce query param
  * close the settings form when done
  * Design changes as per ticket
  * Start tracking langauge in server info
  * Right align the primary button in the enable support modal
  * Re-factor the directory filesize code to use a single array instead of thoussands of transients
  * Remove the warning that would show if you were using anything other than the latest stable, it's no longer needed now that the FAQ is local
  * switch to using a single transient to store directory filesize data
  * Merge pull request #552 from humanmade/issue-552
  * Merge pull request #562 from humanmade/enhancement/issue-562
  * Add an anchor link
  * Add error message
  * Rename function
  * Prefix GET params
  * Update exclude rule action
  * Adds function for building admin action urls
  * fix vertical scroll
  * Adds some functions to manage settings form submission errors
  * Rename nonce and action
  * fetch errors to display
  * Form submission handling for BWP and add-ons settings
  * Use a new function that persists form submission errors to  a transient
  * None check
  * Pass the nonce around in the ajax request
  * Enable support action links
  * Check nonces
  * Modify action URLs to use the admin_post hook
  * Remove unneeded code
  * New line at end
  * Use admin_post hook
  * Merge pull request #502 from humanmade/stream-integration
  * Merge pull request #554 from humanmade/fix-display-schedule-time
  * Display schedule start time in local timezone
  * Update tests shell script
  * Update tests config
  * Make it clear we want one week
  * Display our notices - still WIP
  * Add a class to track common errors and their nice message
  * Add a notices class.
  * Reload the page to display notices
  * Set our notices option in the database with the backup errors.
  * Handle the dismiss action for backup errors
  * Include the notices and errors classes
  * Add a singleton to handle known errors thrown by backups
  * Add remaining disk space
  * Merge branch 'fix-transients' into nice-errors
  * Merge branch 're-design' into nice-errors
  * Merge branch 'master' into nice-errors
  * Set BWP WPR web hook url to live url
  * BWP webhooks - 2nd iteration
  * Add an action hook that gives access to consumers to the backup progress
  * add fake endpoint
  * Configure WP Remote webhook on instantiation
  * Inject the schedule to the constructor
  * JSON encode body
  * Sanitize URL
  * Encrypt the header with WPR key
  * Fix property name
  * Fix property name
  * Return errors
  * Add the remote post action
  * Start on the remote post
  * Display and validate settings
  * Include webhook class
  * Begin a webhook class
  * Beginnings of a HMBKP_Error class

n.n.n / 2014-11-20
==================

  * remove uneeded images
  * latest hm-backup
  * latest hm-backup
  * Typos in v 3.0 changelog
  * Specify POT filename in grunt task
  * Update POT file
  * Markdown readme
  * Regenerate readme
  * Add plugin headers
  * Merge branch 'master' of github.com:humanmade/backupwordpress
  * Refactor the recursive filesize scanner
  * correct text domain
  * Merge pull request #556 from humanmade/issue-556
  * Merge pull request #580 from humanmade/issue-580
  * Merge pull request #584 from humanmade/update-backdrop
  * Remove unneede statements
  * reload the excludes filelist in the correct place when someone excludes a file
  * Latest backdrop
  * Regenerate minified CSS
  * Regenerate minified JS
  * Fix cancel backup action
  * WordPress Coding Standards
  * Exit early if incompatible version of WordPress
  * Update translations
  * Update changelog
  * Merge pull request #573 from humanmade/design
  * Load minified CSS
  * Formatting
  * Biweekly wording
  * Add missing period
  * use nonce_url instead of manually adding the nonce query param
  * close the settings form when done
  * Design changes as per ticket
  * Start tracking langauge in server info
  * Right align the primary button in the enable support modal
  * Re-factor the directory filesize code to use a single array instead of thoussands of transients
  * Remove the warning that would show if you were using anything other than the latest stable, it's no longer needed now that the FAQ is local
  * switch to using a single transient to store directory filesize data
  * Merge pull request #552 from humanmade/issue-552
  * Merge pull request #562 from humanmade/enhancement/issue-562
  * Add an anchor link
  * Add error message
  * Rename function
  * Prefix GET params
  * Update exclude rule action
  * Adds function for building admin action urls
  * fix vertical scroll
  * Adds some functions to manage settings form submission errors
  * Rename nonce and action
  * fetch errors to display
  * Form submission handling for BWP and add-ons settings
  * Use a new function that persists form submission errors to  a transient
  * None check
  * Pass the nonce around in the ajax request
  * Enable support action links
  * Check nonces
  * Modify action URLs to use the admin_post hook
  * Remove unneeded code
  * New line at end
  * Use admin_post hook
  * Merge pull request #502 from humanmade/stream-integration
  * Merge pull request #554 from humanmade/fix-display-schedule-time
  * Display schedule start time in local timezone
  * Add an action hook that gives access to consumers to the backup progress
