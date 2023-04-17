# Default WordPress plugins

Search for things added by plugins and things that make up a plugin.
https://plugintests.com/search-ids

### For core

```bash
export WPSZV="https://github.com/szepeviktor/wordpress-plugin-construction/raw/master"
mkdir wp-content/mu-plugins/

# InnoDB table engine
wget -qO- https://github.com/szepeviktor/debian-server-tools/raw/master/mysql/alter-table.sql \
  | mysql -N $(wp eval 'echo DB_NAME;') | mysql

# no parent themes
wget -P wp-content/mu-plugins/ https://github.com/szepeviktor/wordpress-website-lifecycle/raw/master/mu-plugins/_core-child-themes.php

# disable updates
wget -P wp-content/mu-plugins/ ${WPSZV}/mu-disable-updates/disable-updates.php

# disable comments
wget -P wp-content/mu-plugins/ https://github.com/WPDevelopers/disable-comments-mu/raw/master/disable-comments-mu.php
wget -P wp-content/mu-plugins/disable-comments-mu/ https://github.com/WPDevelopers/disable-comments-mu/raw/master/disable-comments-mu/comments-template.php

# disable feeds
#wp plugin install disable-feeds --activate

# disable embeds
#wp plugin install disable-embeds --activate

# smilies
wp plugin install classic-smilies --activate

# multilanguage
wp plugin install polylang --activate

# mail
wget -P wp-content/mu-plugins/ https://github.com/szepeviktor/wordpress-website-lifecycle/raw/master/mu-plugins/_core-mail.php
#wp plugin install wp-mailfrom-ii smtp-uri --activate
# define( 'SMTP_URI', 'smtp://FOR-THE-WEBSITE%40DOMAIN.TLD:PWD@localhost' );
wp plugin install wp-mailfrom-ii --activate
#wget -P wp-content/mu-plugins/ https://github.com/danielbachhuber/mandrill-wp-mail/raw/master/mandrill-wp-mail.php
wp eval 'var_dump(wp_mail("admin@szepe.net","First outgoing",site_url()));'
```

### Security

```bash
# users/login

#wp plugin install password-bcrypt
#cp -v wp-content/plugins/password-bcrypt/wp-password-bcrypt.php wp-content/mu-plugins/
#wp plugin uninstall password-bcrypt
composer require typisttech/wp-password-argon-two
# sessions
wp plugin install user-session-control --activate
# pwned passwords
wp plugin install disallow-pwned-passwords --activate
# user roles
wp plugin install user-role-editor --activate
# KeePass button
wget -P wp-content/mu-plugins/ ${WPSZV}/mu-keepass-button/keepass-button.php

# WAF for WordPress

composer require szepeviktor/waf4wordpress
wget -P wp-content/mu-plugins/ https://github.com/szepeviktor/wordpress-website-lifecycle/raw/master/mu-plugins/waf4wordpress.php

# security suite + audit

# logbook
wp plugin install logbook --activate
# audit
wp plugin install wp-user-activity --activate
# simple audit
wp plugin install simple-history --activate
# Sucuri
#wp plugin install custom-sucuri sucuri-scanner --activate

# prevent spam

# installation: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-nofollow-robot-trap
wget -P wp-content/mu-plugins/ ${WPSZV}/mu-nofollow-robot-trap/nofollow-robot-trap.php
# CF7 robot trap
wget -P wp-content/plugins/contact-form-7-robot-trap/ ${WPSZV}/contact-form-7-robot-trap/cf7-robot-trap.php
# Comment form robot trap
wget -P wp-content/plugins/comment-form-robot-trap/ ${WPSZV}/comment-form-robot-trap/comment-form-robot-trap.php
# Email address encoder
wp plugin install email-address-encoder --activate
# Stop spammers
#wp plugin install stop-spammer-registrations-plugin --activate

# SVG upload and sanitization

# Safe SVG
composer require darylldoyle/safe-svg
```

### Restrictions

```bash
# lock session IP
wget -P wp-content/mu-plugins/ ${WPSZV}/mu-lock-session-ip/lock-session-ip.php

# concurrent logins
#wp plugin install prevent-concurrent-logins --activate

# weak passwords
wget -P wp-content/mu-plugins/ ${WPSZV}/mu-disallow-weak-passwords/disallow-weak-passwords.php

# user email addresses
wget -P wp-content/mu-plugins/ ${WPSZV}/mu-banned-email-addresses/banned-email-addresses.php

# media
wget -P wp-content/mu-plugins/ ${WPSZV}/mu-image-upload-control/image-upload-control.php
wget -P wp-content/mu-plugins/ ${WPSZV}/mu-image-upload-control/image-upload-control-hu.php

# protect plugins
#wget -P wp-content/mu-plugins/ ${WPSZV}/mu-protect-plugins/protect-plugins.php
```

### Object cache

```php
// In wp-config.php
define( 'WP_CACHE_KEY_SALT', 'SITE-SHORT_' );
$redis_server = array(
    'host'     => '127.0.0.1',
    'port'     => 6379,
    'auth'     => 'secret',
    'database' => 0,
);
```

```bash
wget -P wp-content/mu-plugins/ ${WPSZV}/mu-cache-flush-button/flush-cache-button.php

# Redis @danielbachhuber
wp plugin install wp-redis --activate
wp redis enable
wp transient delete-all

# Memcached @HumanMade
wget -P wp-content/ https://github.com/humanmade/wordpress-pecl-memcached-object-cache/raw/master/object-cache.php
wp transient delete-all

# File-based @emrikol from Automattic
#wp plugin install focus-object-cache
wget -P wp-content/ ${WPSZV}/focus-cache/object-cache.php
wp transient delete-all

# FileSystem, Sqlite, APC/u, Memcached, Redis @inpsyde
# See https://github.com/inpsyde/WP-Stash (inpsyde/wp-stash:dev-master) and https://www.stashphp.com/Drivers.html

# Tiny cache
wget -P wp-content/mu-plugins/ https://github.com/szepeviktor/tiny-cache/raw/master/tiny-translation-cache.php
wget -P wp-content/mu-plugins/ https://github.com/szepeviktor/tiny-cache/raw/master/tiny-nav-menu-cache.php
wget -P wp-content/mu-plugins/ https://github.com/szepeviktor/tiny-cache/raw/master/tiny-cache.php
```

Redis object cache as a service:
[Free 30 MB Redis instance by redislab](https://redislabs.com/redis-cloud)

### Optimize HTML + HTTP

Resource optimization

```bash
# JPEG image quality
# add_filter( 'jpeg_quality', function ( $quality ) { return 91; } );

# Resource Versioning
wp plugin install resource-versioning --activate

# Tiny CDN
wp plugin install tiny-cdn --activate

# Minit
#wp plugin install https://github.com/kasparsd/minit/archive/master.zip
#wp plugin install https://github.com/markoheijnen/Minit-Pro/archive/master.zip

# Safe Redirect Manager
wp plugin install safe-redirect-manager --activate

# WP-FFPC
# backends: APCu, Memcached with ngx_http_memcached_module
# https://github.com/petermolnar/wp-ffpc
#wp plugin install https://github.com/petermolnar/wp-ffpc/archive/master.zip --activate

## Autoptimize - CONFLICTS with resource-versioning
##     define( 'AUTOPTIMIZE_WP_CONTENT_NAME', '/static' );
#wp plugin install autoptimize --activate

#https://github.com/optimalisatie/above-the-fold-optimization
#https://github.com/o10n-x
```

Set up CDN.

### Plugin fixes

MU Plugin Template

`custom-PROJECT-NAME.php`

```php
<?php
/*
 * Plugin Name: customizations (MU)
 * Version: 0.0.0
 * Description: This MU plugin contains customizations.
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle/blob/master/Plugins.md
 * Author: Viktor Szépe
 */
```

See [/mu-plugins/](/mu-plugins/) directory for its content.

### Plugin authors with enterprise mindset

- [Daniel Bachhuber](https://profiles.wordpress.org/danielbachhuber/#content-plugins)
  &bull; [GitHub](https://github.com/danielbachhuber?tab=repositories&type=source)
- [John Blackbourn](https://profiles.wordpress.org/johnbillion#content-plugins)
  &bull; [GitHub](https://github.com/johnbillion?tab=repositories&type=source)
- [Ben Huson](https://profiles.wordpress.org/husobj/#content-plugins)
  &bull; [GitHub](https://github.com/benhuson?utf8=✓&tab=repositories&q=&type=source)
- [10up](https://profiles.wordpress.org/10up#content-plugins)
  &bull; [GitHub](https://github.com/10up?utf8=%E2%9C%93&q=&type=source)
- [Inpsyde](https://profiles.wordpress.org/inpsyde#content-plugins)
  &bull; [GitHub](https://github.com/inpsyde?utf8=%E2%9C%93&q=&type=source)
- [Andrew Norcross](https://profiles.wordpress.org/norcross#content-plugins)
  &bull; [GitHub](https://github.com/norcross?utf8=%E2%9C%93&tab=repositories&q=&type=source)
- [XWP](https://profiles.wordpress.org/xwp#content-plugins)
  &bull; [GitHub](https://github.com/xwp?utf8=✓&q=&type=source&)
- [Frankie Jarrett](https://profiles.wordpress.org/fjarrett#content-plugins)
  &bull; [GitHub](https://github.com/fjarrett?utf8=%E2%9C%93&tab=repositories&q=&type=source)
- [Weston Ruter](https://profiles.wordpress.org/westonruter#content-plugins)
  &bull; [GitHub](https://github.com/westonruter?utf8=✓&tab=repositories&q=&type=source)
- [Scott Kingsley Clark](https://profiles.wordpress.org/sc0ttkclark#content-plugins)
  &bull; [GitHub](https://github.com/sc0ttkclark?utf8=✓&tab=repositories&q=&type=source)
- [Voce Platforms](https://profiles.wordpress.org/voceplatforms#content-plugins)
  &bull; [GitHub](https://github.com/voceconnect?utf8=✓&q=&type=source)
- [interconnect/it](https://profiles.wordpress.org/interconnectit#content-plugins)
  &bull; [GitHub](https://github.com/interconnectit?utf8=✓&q=&type=source)
- [Zack Tollman](https://profiles.wordpress.org/tollmanz#content-plugins)
  &bull; [GitHub](https://github.com/tollmanz?utf8=✓&tab=repositories&q=&type=source)
