# WordPress internals

## WordPress core files in order of execution

- index.php
- wp-blog-header.php
- wp-load.php
- **wp-config.php**
- wp-settings.php
- wp-includes/version.php
- wp-includes/compat.php
- wp-includes/load.php
- _wp-includes/class-wp-paused-extensions-storage.php_
- _wp-includes/class-wp-exception.php_
- _wp-includes/class-wp-fatal-error-handler.php_
- _wp-includes/class-wp-recovery-mode-cookie-service.php_
- _wp-includes/class-wp-recovery-mode-key-service.php_
- _wp-includes/class-wp-recovery-mode-link-service.php_
- _wp-includes/class-wp-recovery-mode-email-service.php_
- _wp-includes/class-wp-recovery-mode.php_
- _wp-includes/error-protection.php_
- wp-includes/default-constants.php

List made with `get_included_files()`.

## WordPress Entry Points

| context | entry point | function |
| ------- | ----------- | -------- |
| front-end | wp-blog-header.php:12 | `!is_admin()` |
| admin | GET request: wp-admin/admin.php:30 | `is_admin()` |
| admin | POST request: wp-admin/admin-post.php:15 | `is_admin()` |
| admin | upload: wp-admin/async-upload.php:16 | `@$_SERVER['SCRIPT_FILENAME'] === ABSPATH . 'wp-admin/async-upload.php'` |
| AJAX call | wp-admin/admin-ajax.php:20 | `defined('DOING_AJAX') && DOING_AJAX` |
| WordPress cron webserver/CLI | wp-cron.php:26 | `defined('DOING_CRON') && DOING_CRON` / `php_sapi_name() === 'cli'` |
| XML-RPC protocol | xmlrpc.php:29 | `defined('XMLRPC_REQUEST') && XMLRPC_REQUEST` |

### Frontend low priority

| context | entry point | function |
| ------- | ----------- | -------- |
| front-end | wp-comments-post.php:16 | `@$_SERVER['SCRIPT_FILENAME'] === ABSPATH . 'wp-comments-post.php'` |
| front-end | wp-trackback.php:12 | `is_trackback()` |

### Admin low priority

| context | entry point | function |
| ------- | ----------- | -------- |
| admin | wp-login.php:12 | ??? |
| admin | wp-signup.php:4 | ??? |
| admin | wp-mail.php:11 | ??? |

### Only during core installation and upgrade

| context | entry point | function |
| ------- | ----------- | -------- |
| admin | wp-admin/install.php:36 | `defined('WP_INSTALLING') && WP_INSTALLING` |
| admin | wp-admin/upgrade.php:18 | `defined('WP_INSTALLING') && WP_INSTALLING` |
| admin | wp-activate.php:12 | `defined('WP_INSTALLING') && WP_INSTALLING` |
| admin | wp-admin/maint/repair.php:10 | `defined('WP_REPAIRING') && WP_REPAIRING` |

### Excluded from profiling

| context | entry point | function |
| ------- | ----------- | -------- |
| front-end | wp-links-opml.php:15 | ??? |
| front-end | wp-includes/ms-files.php:12 | ??? |
| front-end | wp-includes/js/tinymce/wp-mce-help.php:9 | ??? |
| admin | wp-admin/install-helper.php:39 | ??? |
| admin | wp-admin/moderation.php:10 | ??? |

Total: **21 entry points** as of version 3.9.1

See [WP trac #28364](https://core.trac.wordpress.org/ticket/28364)
