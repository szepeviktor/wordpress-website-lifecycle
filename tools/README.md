# WP-CLI tools

This directory catalogs standalone WP-CLI commands for inspecting and
maintaining WordPress installations. Load individual PHP files with
`wp --require=<file>`, or copy the entries from [wp-cli.yml](wp-cli.yml) into a
website's WP-CLI configuration.

## Auditing

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

### [missing-cron-hooks.php](missing-cron-hooks.php)

`wp cron missing-hooks`

Lists scheduled WP-Cron events that have no registered callback.

### [non-silent-indices.php](non-silent-indices.php)

`wp core non-silent-indices`

Finds `index.php` files under `WP_CONTENT_DIR` that contain executable code or
output.

## Core and media operations

### [latest-core-version.php](latest-core-version.php)

`wp core latest-version [--branch=<major.minor>]`

Prints the latest stable WordPress core version, optionally within one release
branch.

### [resize-original-media.php](resize-original-media.php)

`wp media resize-originals [--dry-run]`

Resizes oversized original JPEG, PNG, and WebP uploads while retaining backup
files.
