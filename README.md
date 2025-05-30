# WordPress website lifecycle

[![Tweet](https://img.shields.io/badge/Tweet-share-d5d5d5?style=social&logo=twitter)](https://twitter.com/intent/tweet?text=Here%20is%20how%20to%20run%20WordPress&url=https%3A%2F%2Fgithub.com%2Fszepeviktor%2Fwordpress-website-lifecycle)

How to work with WordPress. :bulb:
`#active-development` `#self-hosted` `#long-term` `#monitoring`

We run WordPress on Debian GNU/Linux operating system
which runs on an UpCloud cloud instance.

## Division of labour

Who does what.

1.  Editor manages the content and related settings.
2.  Developer commits plugin and theme source code to GitHub
    and initiates deployment (CI/CD).
3.  Viktor manages the operating system, webserver software,
    WordPress core, plugins, the theme, privileged users,
    system settings, security, backup and migrations.

There is no web-based administration.
WordPress installation is managed with git, **Composer** and WP-CLI on the command-line.

## More than the famous 5-minute installation

Our WordPress installation includes preparations for the next few **error-free years**.

These preparations are implemented in [MU plugins](/mu-plugins/).

## Support my work

Please consider sponsoring me monthly if you use my packages in an agency.

[![Sponsor](https://github.com/szepeviktor/.github/raw/master/.github/assets/github-like-sponsor-button.svg)](https://github.com/sponsors/szepeviktor)

They are already sponsoring me.

<!-- markdownlint-disable MD033 -->
[<img src="https://github.com/johnbillion.png" width="50px" alt="johnbillion">](https://github.com/johnbillion)&nbsp;&nbsp;
[<img src="https://github.com/JJJ.png" width="50px" alt="JJJ">](https://github.com/JJJ)&nbsp;&nbsp;
[<img src="https://github.com/montchr.png" width="50px" alt="montchr">](https://github.com/montchr)&nbsp;&nbsp;
[<img src="https://github.com/blockifywp.png" width="50px" alt="blockifywp">](https://github.com/blockifywp)&nbsp;&nbsp;
<!-- markdownlint-enable MD033 -->

## Working in a git repository

Our WordPress installation is stored in a git repository
and managed with Composer.

This is the starter template.
[szepeviktor/composer-managed-wordpress](https://github.com/szepeviktor/composer-managed-wordpress)

Custom plugins and themes live in separate git repositories.

**Purchased themes** should have an active child theme.

```bash
wp plugin install child-theme-configurator --activate
```

Keep the child theme in a git repository also.

## Onboarding for developers

Let's prevent working against each other!

-   Don't write code changing WordPress core behavior anywhere else than **MU plugins**,
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
-   Plugin update check HTTP requests and updates themselves are disabled
    because the entire WordPress installation is **managed with Composer**
-   Plugin and theme update and WordPress management-related admin pages are removed
    (updated with Composer, administered with WP-CLI)
-   WP-Cron is ran by a Linux cron job (the default pseudo cron/web callback is disabled)
-   Only things necessary for generating custom admin pages
    and generating HTML go into the **theme**
-   Business logic (e.g. processing input from visitors) goes into **plugins**
-   Please adhere to a coding standard of **your choice**
-   Please avoid [discouraged functions](https://github.com/szepeviktor/phpcs-psr-12-neutron-hybrid-ruleset/blob/master/PSR12NeutronRuleset/ruleset.xml#L11)
-   We run static analysis on all source code
-   PSR-4 autoloading is suggested (no need for `require` and custom class autoloading)
-   WordPress core is installed in a separate subdirectory
-   Please also see [hosting information for developers](https://github.com/szepeviktor/debian-server-tools/blob/master/Onboarding.md#onboarding-for-developers)

## High level overview

:bulb:

- [Hosting](https://github.com/szepeviktor/infrastructure-for-hosting-web-applications)
- [Software stack](/WordPress-stack.md)
- [Performance](/WordPress-performance.md)
- [Security](/WordPress-security.md)
- [Production environment](https://github.com/szepeviktor/debian-server-tools/blob/master/webserver/Production-website.md)

## Installation materials

-   WordPress installation: [szepeviktor/composer-managed-wordpress](https://github.com/szepeviktor/composer-managed-wordpress)
-   [`wp-config`](/wp-config)
-   _Alternative [WP-CLI installation](WP-CLI-installation.md)_
-   MU plugins for core, theme and plugins: [/mu-plugins/](/mu-plugins)
    and how to install [Default plugins](/Plugins.md)
-   Starter plugin: [szepeviktor/starter-plugin](https://github.com/szepeviktor/starter-plugin)
-   Starter theme: [timber/starter-theme](https://github.com/timber/starter-theme/tree/2.x)
-   Starter child theme: [/divi-child/](/divi-child)
-   Feature plugins: [szepeviktor/wordpress-plugin-construction](https://github.com/szepeviktor/wordpress-plugin-construction)

## Plugin and Theme review

- `wp plugin install https://github.com/WordPress/plugin-check/archive/refs/heads/trunk.zip`
- `wordpress/plugin-check-action` GitHub action
- `wp plugin install https://github.com/WordPress/theme-check/archive/refs/heads/master.zip`
- `Wordpress/theme-review-action` GitHub action
- [unofficial fork of Theme Check plugin](https://themecheck.info/)
- [WPThemeReview Standard for PHP_CodeSniffer](https://github.com/WPTRT/WPThemeReview)
- [PHP Insights](https://github.com/nunomaduro/phpinsights)

## Development stuff

- [Leaving legacy technologies behind](https://github.com/szepeviktor/starter-plugin)
- [Hooks in WordPress](/WordPress-hooks.md)
- [OOP for WordPress](/WordPress-OOP.md)
- Tools for development: [SentencePress](https://github.com/szepeviktor/SentencePress)
- `wp_mail` emails https://github.com/johnbillion/wp_mail

## Testing

- [Query Monitor](https://github.com/johnbillion/query-monitor)
- [OPcache status](https://github.com/szepeviktor/qm-opcache-status)
- [Test Mode](https://github.com/szepeviktor/test-mode)
- Unit tests without a database https://github.com/Automattic/wordbless
- [WP_Mock](https://github.com/10up/wp_mock)
- [Codeception](https://github.com/lucatume/wp-browser) (BDD)
