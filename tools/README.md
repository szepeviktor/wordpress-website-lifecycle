# WP-CLI tools

This directory catalogs standalone WP-CLI commands for inspecting and
maintaining WordPress installations. Load individual PHP files with
`wp --require=<file>`, or copy the entries from [wp-cli.yml](wp-cli.yml) into a
website's WP-CLI configuration.

## Auditing

### [attachment-size-audit.php](attachment-size-audit.php)

`wp media audit-sizes [<id>] [--all-sizes] [--include-ok] [--format=<format>]`

Audits image attachment metadata, database versus WordPress postmeta consistency,
missing image files, and stored dimensions without modifying files or metadata.
Checks WordPress and active WooCommerce default sizes. Pass `--all-sizes` to check
all registered and stored sizes, `--include-ok` to include successful file checks,
or an attachment ID to check one image. Returns exit code 1 when issues are found.

### [db-tail.php](db-tail.php)

`wp db tail <table> --cursor=<column> [--columns=<columns>] [--where=<expression>]`

Prints new rows from an append-only table as JSON lines. Omit the WordPress
table prefix and use a unique, increasing, nonnegative integer cursor.

```shell
wp --require=tools/db-tail.php db tail wpmailsmtp_debug_events --cursor=id --columns=id,created_at,content
```

The first run reads all matching rows; later runs resume from an autoload-disabled
`db_tail_<sha256>` option derived from the source and query parameters. Reads in
batches of 1000 until no rows remain. `--where` accepts trusted SQL.

Run sequentially: there is no locking or output acknowledgement. Updates,
deletions and late commits below the saved cursor are not tracked. Delete the
state option to restart after a table reset or database restore.

### [invalid-acf-related-items.php](invalid-acf-related-items.php)

`wp acf invalid-related-items <meta-key-suffix>`

Finds ACF relationship field values pointing to unavailable posts.

### [invalid-format-characters.php](invalid-format-characters.php)

`wp db invalid-format-characters`

Reports Unicode Format characters found in database text columns without
modifying their contents. Pass `--ignore-emoji-joiners` to ignore U+200D
joiners in valid emoji sequences.

### [invalid-language-files.php](invalid-language-files.php)

`wp core invalid-language-files`

Finds plugin and theme translation files whose locale is not advertised by
WordPress.org.

### [invalid-media-filenames.php](invalid-media-filenames.php)

`wp media invalid-filenames`

Finds uploads with filenames containing characters outside the portable ASCII
set.

### [missing-media-files.php](missing-media-files.php)

`wp media missing-files [--dry-run]`

Deletes attachment posts whose original media file is missing from disk. Pass
`--dry-run` to list affected attachments without deleting them.

### [missing-cron-hooks.php](missing-cron-hooks.php)

`wp cron missing-hooks`

Lists scheduled WP-Cron events that have no registered callback.

### [non-silent-indices.php](non-silent-indices.php)

`wp core non-silent-indices`

Finds `index.php` files under `WP_CONTENT_DIR` that contain executable code or
output.

### [web-php-version.php](web-php-version.php)

`wp core web-php-version`

Prints the PHP version used by the web server through a temporary REST API
endpoint.

## Core and media operations

### [latest-core-version.php](latest-core-version.php)

`wp core latest-version [--branch=<major.minor>]`

Prints the latest stable WordPress core version, optionally within one release
branch.

### [resize-original-media.php](resize-original-media.php)

`wp media resize-originals [--dry-run]`

Resizes oversized original JPEG, PNG, and WebP uploads while retaining backup
files.
