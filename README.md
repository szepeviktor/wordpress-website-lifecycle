# WordPress website lifecycle

### Division of labour

Who does what.

1. Editor manages the content and related settings.
2. Developer commits plugin and theme source code to GitHub and initiates deployment (CI/CD).
3. Viktor manages WordPress core, plugins, the theme,
   priviledged users, system settings, security, backup and migrations.

There is no web based administration.
WordPress installation is managed with git, **Composer** and WP-CLI on the command-line.

### More than the famous 5-minute installation

Our WordPress installation includes preparations for the next few **error-free years**.

These preparations are implemented in [MU plugins](/mu-plugins/).

### Working in a git repository

Our WordPress installation is stored in a git repository
and managed with Composer.

This is the starter template.
[szepeviktor/composer-managed-wordpress](https://github.com/szepeviktor/composer-managed-wordpress)

Custom plugins and themes live in separate git repositories.

### Onboarding for developers

Let's prevent working against each other!

- Don't write code changing WordPress core behavior anywhere else than **MU plugins**,
  - removing admin menus, admin bar elements
  - disabling emojis
  - disabling comments
  - disabling feeds
  - disabling embeds
  - mail settings and logging
  - WAF: authentication/login, HTTP and REST API security
  - comment form and contact form spam traps
  - media management
  - nav menu, translation and content caching
  - HTTP and HTML optimization
  - CDN support
- Plugin update check HTTP requests and updates itself are disabled
  because the whole WordPress installation is **managed with Composer**
- Plugin and theme update and WordPress management-related admin pages are removed
  (updated with Composer, administered with WP-CLI)
- WP-Cron is ran by a linux cron job (the default pseudo cron/web callback is disabled)
- Only things necessary for generating custom admin pages
  and generating HTML go into the **theme**
- Business logic (e.g. processing input from visitors) goes into **plugins**
- Please adhere to a coding standard of **your choice**
- Please avoid [discouraged functions](/webserver/laravel/phpcs.xml)
- We run static analysis on all source code
- PSR-4 autoloading is suggested (no need for `require` and custom class autoloading)
- WordPress core is installed in a separate subdirectory
- Please also see [hosting information for developers](/Onboarding.md#onboarding-for-developers)

### Use child theme

Purchased themes can be customized using a child theme.

```bash
wp theme install page-builder-framework --activate
wp plugin install child-theme-configurator --activate
```

Keep the child theme in a git repository.

### On deploy and Staging->Production migration

@TODO Move to Production-website.md

Also in /webserver/Production-website.md

- `wp transient delete-all`
- `wp db query "DELETE FROM $(wp eval 'global $table_prefix;echo $table_prefix;')options WHERE option_name LIKE '%_transient_%'"`
- Remove development wp_options -> Option Inspector
- Delete unused Media files @TODO `for $m in files; search $m in DB;`
- `wp db optimize`
- WP-Sweep


#### Settings

- General Settings
- Writing Settings
- Reading Settings
- Media Settings
- Permalink Settings
- WP Mail From


#### Search & replace items

```bash
wp search-replace --precise --recurse-objects --all-tables-with-prefix ...
```

1. https://DOMAIN.TLD (no trailing slash)
1. /home/PATH/TO/SITE (no trailing slash)
1. EMAIL@ADDRESS.ES (all addresses)
1. DOMAIN.TLD (now without https)

And manually replace constants in `wp-config.php`

Web-based search & replace tool:

```bash
wget -O srdb.php https://github.com/interconnectit/Search-Replace-DB/raw/master/index.php
wget https://github.com/interconnectit/Search-Replace-DB/raw/master/srdb.class.php
```

##### Where script kiddies look for WordPress

- /backup/
- /blog/
- /cms/
- /demo/
- /dev/
- /home/
- /main/
- /new/
- /old/
- /portal/
- /site/
- /test/
- /tmp/
- /web/
- /wordpress/
- /wp/

## Other resources

- Webserver stack https://github.com/szepeviktor/debian-server-tools/blob/master/CV-WordPress.md#webserver-stack
- WordPress performance https://github.com/szepeviktor/tiny-cache#wordpress-performance
- Development environment: [/webserver/WP-config-dev.md](/webserver/WP-config-dev.md)
- Development tools: `szepeviktor/wordpress-sitebuild` repo
- Production environment: [/webserver/Production-website.md](/webserver/Production-website.md)
- Production on cPanel and migration to cPanel: [shared-hosting-aid/cPanel/README.md](https://github.com/szepeviktor/shared-hosting-aid/blob/master/cPanel/README.md)
- Content plugins: [wordpress-plugin-construction/README.md](https://github.com/szepeviktor/wordpress-plugin-construction/blob/master/README.md)
- WordPress installation: standard, subdirectory (optionally using git) [in this document](#standard-directory-structure)
- WordPress migration: dev->live, live->other domain [/webserver/Production-website.md](/webserver/Production-website.md#migration)

