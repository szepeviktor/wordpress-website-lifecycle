# MU plugins

This directory catalogs standalone must-use plugins and related operations
notes. Install only the required PHP files into a website's
`wp-content/mu-plugins/` directory.

## Development

### [devberry.php](devberry.php)

> Development and staging websites can be indexed or mistaken for production
> by visitors.

Discourages search engines, requires visitors to log in, and displays a purple
environment marker with the active theme name and version.

## Security and access

### [_core-disable-login.php](_core-disable-login.php)

> Public authentication may need to be suspended without taking the website offline.

Rejects every WordPress username and password authentication attempt.

### [_core-pingback.php](_core-pingback.php)

> XML-RPC pingbacks can reveal the origin IP address behind a firewall or
> reverse proxy.

Removes the `pingback.ping` XML-RPC method.

### [_core-username.php](_core-username.php)

> Whitespace in usernames creates confusing and error-prone account identifiers.

Removes whitespace while WordPress performs strict username sanitization.

### [waf4wordpress.php](waf4wordpress.php)

> Malicious HTTP requests can reach WordPress before application-level defenses
> inspect them.

Initializes WAF for WordPress core event handling and exposes its request
analyzer as a drop-in on the Plugins screen.

### [_robots-gptbot.php](_robots-gptbot.php)

> Automated AI crawling can consume resources or reuse content against the
> publisher's preference.

Adds a site-wide disallow rule for GPTBot to `robots.txt` on public websites.

### [_robots-oszkbot.php](_robots-oszkbot.php)

> Automated web archiving can capture content against the website owner's preference.

Adds a site-wide disallow rule for OSZKbot to `robots.txt` on public websites.

## WordPress administration

### [_core-debrand-wordpress.php](_core-debrand-wordpress.php)

> WordPress branding and informational screens distract users from managing
> their website.

Removes the WordPress toolbar menu and footer text, and redirects the About,
Credits, Freedoms, Privacy, and Contribute screens.

### [_core-admin-email-confirm.php](_core-admin-email-confirm.php)

> Periodic administrator email confirmation interrupts routine work on
> centrally maintained websites.

Disables the WordPress administrator email confirmation reminder.

### [_core-child-themes.php](_core-child-themes.php)

> Activating a parent theme while its child theme is available can silently
> bypass site customizations.

Reverts the activation and displays an administrator notice when an installed
child theme should be used instead.

### [_core-disable-command-palette.php](_core-disable-command-palette.php)

> An unused command palette adds unnecessary assets and another interface for
> administrators to learn.

Prevents WordPress from enqueueing command palette assets.

### [_core-disable-custom-css.php](_core-disable-custom-css.php)

> CSS edited in the Customizer bypasses the version-controlled theme deployment
> process.

Removes the Additional CSS section from the Customizer.

### [_core-disallow-site-health.php](_core-disallow-site-health.php)

> Site Health exposes diagnostics that are not actionable for users of a
> centrally managed website.

Revokes access to Site Health checks and replaces the core Site Health service
with a no-op implementation.

### [_core-disallow-updates.php](_core-disallow-updates.php)

> Administrators can bypass the Composer-managed deployment process.

Removes core, plugin, theme, and translation installation and update
capabilities, and emails the administrator when a plugin is activated or
deactivated.

### [_core-disallowed-plugins.php](_core-disallowed-plugins.php)

> Plugins that duplicate hosting services or introduce unsafe functionality can
> compromise a managed website.

Automatically deactivates listed plugins and prevents administrators from
activating them again.

### [_core-theme-subdir.php](_core-theme-subdir.php)

> Theme archives containing an extra `theme/` directory cannot be installed with
> the standard uploader.

Adjusts source selection during manual theme uploads so WordPress can locate
the actual theme directory.

### [_core-comment.php](_core-comment.php)

> Routine comment moderation emails create unnecessary noise for the site administrator.

Removes the administrator email address from comment moderation recipients.

### [_core-fse.php](_core-fse.php)

> WordPress global style output is redundant when the theme fully controls
> frontend styling.

Removes global stylesheet and SVG filter output from the frontend.

### [_core-jquery-migrate.php](_core-jquery-migrate.php)

> Loading jQuery Migrate for modern frontend code adds unnecessary JavaScript overhead.

Removes jQuery Migrate from the frontend jQuery dependencies.

### [_core-media-file-name.php](_core-media-file-name.php)

> Non-ASCII media filenames create portability and URL-handling problems across
> systems.

Transliterates uploaded and sideloaded media filenames to an ASCII-safe
character set.

### [_core-post-by-email.php](_core-post-by-email.php)

> An unused legacy publishing channel increases configuration and security surface.

Disables the post-by-email configuration interface.

### [_core-reg-new-user.php](_core-reg-new-user.php)

> Administrators receive redundant notifications for routine user registrations.

Sends the new-account notification only to the registered user.

### [_core-slug.php](_core-slug.php)

> Unsupported symbols in slugs can produce unstable or hard-to-share URLs.

Restricts slug input to printable ASCII characters, Unicode letters, and
Unicode numbers before WordPress performs its normal sanitization.

## HTTP and routing

### [_core-http.php](_core-http.php)

> Silent HTTP failures and inappropriate cache headers make integrations harder
> to operate reliably.

Suppresses WordPress HTTPS detection, logs failed outbound requests, and removes
the legacy `Pragma` header from HTTP/1.1 and newer responses.

### [_core-auto-trailing-slash.php](_core-auto-trailing-slash.php)

> Inconsistent trailing slashes create duplicate URLs and fragmented cache entries.

Adds an Apache rewrite rule that redirects extensionless paths to their
trailing-slash form.

### [_core-dot-404.php](_core-dot-404.php)

> Requests for nonexistent file-like paths waste application resources and
> obscure malicious traffic.

Returns an early plain-text 404 response for dotted paths that are not on the
explicit allowlist and logs the request as suspicious.

### [_core-http-query-string-errors.php](_core-http-query-string-errors.php)

> Noncanonical query strings can cause inconsistent request parsing and
> analytics attribution.

Logs query strings containing plus signs, asterisks, or lowercase hexadecimal
percent-encoding.

### [_core-http-reencode-not-encoded-characters.php](_core-http-reencode-not-encoded-characters.php)

> Improperly encoded campaign parameters can be interpreted differently by
> clients and WordPress.

Rebuilds request query parameters using RFC 3986 encoding before normal request
processing.

### [_core-php-bug-50921.php](_core-php-bug-50921.php)

> Fatal PHP errors reported as successful responses can be cached and missed by
> monitoring.

Changes the response status to `500 Internal Server Error` when a fatal error is
detected during shutdown.

## Operations and observability

### [_core-log-deprecated.php](_core-log-deprecated.php)

> Deprecated WordPress APIs can remain unnoticed until an upgrade breaks website
> functionality.

Logs deprecated functions, constructors, files, arguments, and hooks when they
are used.

### [_core-mail.php](_core-mail.php)

> Email failures and implementation-revealing headers reduce operational
> visibility and privacy.

Removes the `X-Mailer` header and sends WordPress mail failures to the PHP and
system logs.

### [_core-migration.php](_core-migration.php)

> A copied website can continue performing production operations on the old
> server after migration.

Labels the old login screen and blocks WP-Cron, outbound HTTP requests, email,
and Action Scheduler execution.

**Caution:** Remove this MU plugin after the migration is complete.

### [_core-cron.php](_core-cron.php)

> A stalled WP-Cron runner can leave scheduled business processes unexecuted
> without a visible signal.

Records the timestamp of the latest WP-Cron invocation for external monitoring.

### [_core-wp-cron-logger.php](_core-wp-cron-logger.php)

> WP-Cron loopback failures and unexpected callback output are otherwise
> difficult to diagnose.

Logs errors and nonempty response bodies from the HTTP request used to spawn
WP-Cron.

## Plugin integrations

### [advanced-custom-fields.php](advanced-custom-fields.php)

> Site users can modify code-managed field groups or encounter irrelevant
> premium promotions.

Hides the ACF administration screens and the field-group premium features
panel.

### [contact-form-7-sender-error.php](contact-form-7-sender-error.php)

> Valid external sender addresses can be blocked by Contact Form 7's site-domain
> validator.

Removes sender-domain validation errors from the primary and secondary mail
configurations.

### [disqus-comment-system.php](disqus-comment-system.php)

> Disqus API failures can silently interrupt comment synchronization.

Logs the response body of unsuccessful Disqus API requests.

### [easy-social-sharing.php](easy-social-sharing.php)

> A bundled premium plugin can attempt updates outside the managed deployment process.

Disables the Easy Social Share Buttons update manager.

### [elementor.php](elementor.php)

> Elementor onboarding, promotions, tracking prompts, and remote services create
> distraction and external dependencies.

Disables tracking, AI, onboarding, promotional modules, remote feeds, upsells,
and related admin noise while retaining the functional editor screens.

### [envato-market.md](envato-market.md)

> ThemeForest software can fall behind when the Envato Market plugin cannot be
> refreshed repeatably.

Provides instructions and a shell script that reinstalls the latest Envato
Market plugin from its GitHub repository.

### [flamingo.php](flamingo.php)

> An unused address book adds menu clutter and exposes an unnecessary contact overview.

Removes the Flamingo Address Book submenu.

### [fluent-smtp.php](fluent-smtp.php)

> Accidental non-production email and promotional admin content create
> operational risk and distraction.

Simulates email outside production and removes FluentSMTP support,
documentation, subscription, and promotional interface elements.

### [gravityforms.php](gravityforms.php)

> Default Gravity Forms behavior permits unmanaged updates, telemetry, noisy
> administration, and inconsistent email output.

Disables update and telemetry services, cleans the administration interface,
normalizes multipart email, moves the honeypot, and delays inline form scripts.

### [hellopack-client.php](hellopack-client.php)

> A backend maintenance plugin adds unnecessary frontend request overhead.

Removes HelloPack from the active plugin list on normal frontend requests while
keeping it active for administration, cron, CLI, REST, and XML-RPC.

### [jetpack.php](jetpack.php)

> Unused Jetpack modules introduce unnecessary external services, processing,
> and admin content.

Removes selected Jetpack modules from availability and hides the Newsletter
dashboard widget.

### [js_composer.php](js_composer.php)

> WPBakery can attempt updates outside the managed deployment process.

Disables the WPBakery Visual Composer update manager and bypasses its purchase
code check.

### [metronet-tag-manager.php](metronet-tag-manager.php)

> Analytics tags must not run before a visitor has granted analytics consent.

Returns an empty Metronet Tag Manager configuration until CookieYes records
analytics consent.

### [polylang.php](polylang.php)

> A setup wizard interrupts repeatable deployment of a preconfigured
> multilingual website.

Defines a no-op Polylang wizard before the plugin attempts to start it.

### [redux-framework.php](redux-framework.php)

> Theme frameworks can make unwanted remote requests and expose unnecessary AJAX
> endpoints.

Suppresses Redux Framework update checks and removes its public and private
AJAX handlers.

### [simple-history-mail.php](simple-history-mail.php)

> Successful outgoing email has no audit trail when only failures are recorded.

Logs successful WordPress email delivery to Simple History or falls back to the
PHP error log.

### [sogo-accessibility.php](sogo-accessibility.php)

> An unnecessary license check creates an external dependency in the
> administration interface.

Blocks the SOGO Accessibility license-check HTTP request.

### [tgmpa.php](tgmpa.php)

> Themes can prompt users to install bundled plugins outside the managed
> deployment process.

Removes procedural and object-oriented TGMPA registration callbacks.

**Caution:** Replace the `CUSTOM-FUNCTION` placeholder before use.

### [the-events-calendar.php](the-events-calendar.php)

> Missing shared translations expose untranslated event-management strings to users.

Loads the `tribe-common` text domain from the WordPress language directory.

### [woocommerce-order-action-resend-completed.php](woocommerce-order-action-resend-completed.php)

> Support staff need to resend a completed-order email without changing the
> order status.

Adds an order action that triggers the customer completed-order notification
and records an order note.

### [woocommerce.php](woocommerce.php)

> WooCommerce's optional administration, promotion, tracking, and background
> features add operational complexity.

Disables selected WooCommerce features, onboarding, promotions, telemetry, and
the Action Scheduler async runner; it also logs failed actions and limits access
to diagnostics.

### [wordpress-seo.php](wordpress-seo.php)

> Promotional support content and unwanted structured data clutter Yoast SEO on
> a managed website.

Removes Yoast linking data, HelpScout, premium upsells, and premium submenu
pages.

### [wp-security-audit-log.php](wp-security-audit-log.php)

> Commercial screens and broad access to audit settings increase admin clutter
> and exposure.

Removes WP Activity Log premium views and restricts its settings to the primary
administrator account.

### [wp-user-activity.php](wp-user-activity.php)

> A dedicated top-level activity menu adds unnecessary WordPress administration
> clutter.

Moves the WP User Activity menu below Dashboard.

### [yith-premium.php](yith-premium.php)

> YITH premium plugins can attempt updates outside the managed deployment process.

Provides a no-op YITH upgrade manager.

## Theme integrations

### [theme-avada.php](theme-avada.php)

> Avada's bundled patcher and updater can modify software outside the managed
> deployment process.

Provides no-op implementations of the Fusion patcher, patch checker, and
updater.

### [theme-unity.md](theme-unity.md)

> Releases of plugins bundled by the Unity theme are difficult to monitor
> through normal WordPress updates.

Provides instructions and a shell script that compares remote modification
dates for the bundled WPBakery and Revolution Slider archives.
