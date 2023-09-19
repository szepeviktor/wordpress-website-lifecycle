# Default WordPress plugins

Search for things added by plugins and things that make up a plugin.
https://plugintests.com/search-ids

`_core-disallow-updates.php` is already in mu-plugins/ directory.
https://github.com/szepeviktor/composer-managed-wordpress/tree/master/public/wp-content/mu-plugins

## For core

<!-- markdownlint-disable MD013 -->
```bash
# Put content_disposition = on into ~/.wgetrc
export LEGACY_PLUGINS_URL="https://github.com/szepeviktor/wordpress-plugin-construction/raw/master"

cd public/wp-content/mu-plugins/

# Use InnoDB table engine
wget -qO- https://github.com/szepeviktor/debian-server-tools/raw/master/mysql/alter-table.sql \
    | mysql -N $(wp eval 'echo DB_NAME;') \
    | mysql

# No parent themes
wget https://github.com/szepeviktor/wordpress-website-lifecycle/raw/master/mu-plugins/_core-child-themes.php

# Disable updates
wget ${LEGACY_PLUGINS_URL}/mu-disable-updates/disable-updates.php

# Disable comments
wget https://github.com/WPDevelopers/disable-comments-mu/raw/master/disable-comments-mu.php
wget -P disable-comments-mu/ https://github.com/WPDevelopers/disable-comments-mu/raw/master/disable-comments-mu/comments-template.php

# Disable feeds
composer require wpackagist-plugin/disable-feeds

# Disable embeds
composer require wpackagist-plugin/disable-embeds

# Smilies
composer require wpackagist-plugin/classic-smilies

# Multilanguage
composer require wpackagist-plugin/polylang

# Mailing
wget https://github.com/szepeviktor/wordpress-website-lifecycle/raw/master/mu-plugins/_core-mail.php
composer require wpackagist-plugin/wp-mailfrom-ii
composer require wpackagist-plugin/smtp-uri
# define( 'SMTP_URI', 'smtp://FOR-THE-WEBSITE%40EXAMPLE.COM:PWD@localhost' );
wget https://github.com/danielbachhuber/mandrill-wp-mail/raw/master/mandrill-wp-mail.php

wp plugin activate --all
wp eval 'var_dump(wp_mail("admin@szepe.net","First outgoing",site_url()));'
```

## Security

```bash
# Users and login

wget https://github.com/szepeviktor/password-bcrypt/raw/wp/wp-password-bcrypt.php
composer require typisttech/wp-password-argon-two
# Sessions
composer require wpackagist-plugin/user-session-control
# Pwned passwords
composer require wpackagist-plugin/disallow-pwned-passwords
# User roles
composer require wpackagist-plugin/user-role-editor
# KeePass button
wget ${LEGACY_PLUGINS_URL}/mu-keepass-button/keepass-button.php

# WAF for WordPress

composer require szepeviktor/waf4wordpress
wget https://github.com/szepeviktor/wordpress-website-lifecycle/raw/master/mu-plugins/waf4wordpress.php

# Security suite + audit

# Logbook
composer require wpackagist-plugin/logbook
# Audit
composer require wpackagist-plugin/wp-user-activity
# Simple audit
composer require wpackagist-plugin/simple-history

# Prevent spam

# Installation: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-nofollow-robot-trap
wget ${LEGACY_PLUGINS_URL}/mu-nofollow-robot-trap/nofollow-robot-trap.php
# CF7 robot trap
wget ${LEGACY_PLUGINS_URL}/contact-form-7-robot-trap/cf7-robot-trap.php
# Comment form robot trap
wget ${LEGACY_PLUGINS_URL}/comment-form-robot-trap/comment-form-robot-trap.php
# Email address encoder
composer require wpackagist-plugin/email-address-encoder
# Stop spammers
composer require wpackagist-plugin/stop-spammer-registrations-plugin

# SVG upload and sanitization

# Safe SVG
composer require darylldoyle/safe-svg
```

## Restrictions

```bash
# Lock session IP
wget ${LEGACY_PLUGINS_URL}/mu-lock-session-ip/lock-session-ip.php

# Concurrent logins
composer require wpackagist-plugin/prevent-concurrent-logins

# Weak passwords
wget ${LEGACY_PLUGINS_URL}/mu-disallow-weak-passwords/disallow-weak-passwords.php

# User email addresses
wget ${LEGACY_PLUGINS_URL}/mu-banned-email-addresses/banned-email-addresses.php

# Media
wget ${LEGACY_PLUGINS_URL}/mu-image-upload-control/image-upload-control.php
wget ${LEGACY_PLUGINS_URL}/mu-image-upload-control/image-upload-control-hu.php
```

## Object cache

```php
// In wp-config.php
define('WP_CACHE_KEY_SALT', 'SITE-SHORT_');
$redis_server = [
    'host'     => '127.0.0.1',
    'port'     => 6379,
    'auth'     => 'secret',
    'database' => 0,
];
```

```bash
# Redis @Pantheon
composer require wpackagist-plugin/wp-redis
wp plugin activate wp-redis
wp redis enable
wp transient delete-all

# Memcached @HumanMade
wget -P ../ https://github.com/humanmade/wordpress-pecl-memcached-object-cache/raw/master/object-cache.php
wp transient delete-all

# File-based @emrikol from Automattic
wget -P ../ ${LEGACY_PLUGINS_URL}/focus-cache/object-cache.php
wp transient delete-all

# FileSystem, Sqlite, APC/u, Memcached, Redis @inpsyde
# See https://github.com/inpsyde/WP-Stash (inpsyde/wp-stash:dev-master) and https://www.stashphp.com/Drivers.html

# Tiny cache
wget https://github.com/szepeviktor/tiny-cache/raw/master/tiny-translation-cache.php
wget https://github.com/szepeviktor/tiny-cache/raw/master/tiny-nav-menu-cache.php
wget https://github.com/szepeviktor/tiny-cache/raw/master/tiny-cache.php
```

Redis object cache as a service:
[Free 30 MB Redis instance by redislab](https://redis.com/redis-enterprise-cloud/overview/)

## Optimize HTML + HTTP

Resource optimization

```bash
# JPEG image quality
# add_filter('jpeg_quality', static function ($quality) {return 91;});

# Resource Versioning
composer require wpackagist-plugin/resource-versioning

# Tiny CDN
composer require wpackagist-plugin/tiny-cdn

# Minit
composer require kasparsd/minit
composer require markoheijnen/minit-pro

# Safe Redirect Manager
composer require wpackagist-plugin/safe-redirect-manager
```

Set up CDN.

## Plugin fixes

MU Plugin Template

`custom-PROJECT-NAME.php`

```php
<?php
/*
 * Plugin Name: customizations (MU)
 * Version: 0.0.0
 * Description: This MU plugin contains customizations.
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle/blob/master/Plugins.md
 */
```

See [/mu-plugins/](/mu-plugins/) directory for MU plugins.

## Plugin authors with enterprise mindset

-   [Daniel Bachhuber](https://profiles.wordpress.org/danielbachhuber/#content-plugins)
    &bull; [GitHub](https://github.com/danielbachhuber?tab=repositories&type=source)
-   [John Blackbourn](https://profiles.wordpress.org/johnbillion#content-plugins)
    &bull; [GitHub](https://github.com/johnbillion?tab=repositories&type=source)
-   [Ben Huson](https://profiles.wordpress.org/husobj/#content-plugins)
    &bull; [GitHub](https://github.com/benhuson?utf8=✓&tab=repositories&q=&type=source)
-   [10up](https://profiles.wordpress.org/10up#content-plugins)
    &bull; [GitHub](https://github.com/10up?utf8=%E2%9C%93&q=&type=source)
-   [Inpsyde](https://profiles.wordpress.org/inpsyde#content-plugins)
    &bull; [GitHub](https://github.com/inpsyde?utf8=%E2%9C%93&q=&type=source)
-   [Andrew Norcross](https://profiles.wordpress.org/norcross#content-plugins)
    &bull; [GitHub](https://github.com/norcross?utf8=%E2%9C%93&tab=repositories&q=&type=source)
-   [XWP](https://profiles.wordpress.org/xwp#content-plugins)
    &bull; [GitHub](https://github.com/xwp?utf8=✓&q=&type=source&)
-   [Frankie Jarrett](https://profiles.wordpress.org/fjarrett#content-plugins)
    &bull; [GitHub](https://github.com/fjarrett?utf8=%E2%9C%93&tab=repositories&q=&type=source)
-   [Weston Ruter](https://profiles.wordpress.org/westonruter#content-plugins)
    &bull; [GitHub](https://github.com/westonruter?utf8=✓&tab=repositories&q=&type=source)
-   [Scott Kingsley Clark](https://profiles.wordpress.org/sc0ttkclark#content-plugins)
    &bull; [GitHub](https://github.com/sc0ttkclark?utf8=✓&tab=repositories&q=&type=source)
-   [Voce Platforms](https://profiles.wordpress.org/voceplatforms#content-plugins)
    &bull; [GitHub](https://github.com/voceconnect?utf8=✓&q=&type=source)
-   [interconnect/it](https://profiles.wordpress.org/interconnectit#content-plugins)
    &bull; [GitHub](https://github.com/interconnectit?utf8=✓&q=&type=source)
-   [Zack Tollman](https://profiles.wordpress.org/tollmanz#content-plugins)
    &bull; [GitHub](https://github.com/tollmanz?utf8=✓&tab=repositories&q=&type=source)
