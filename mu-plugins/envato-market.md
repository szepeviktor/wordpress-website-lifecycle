# Envato Market updater

Use this script to reinstall the Envato Market plugin from its GitHub
repository. This is an operations script, not an MU plugin; do not place it in
`wp-content/mu-plugins/`.

## Instructions

1. Save the snippet as `envato-market-update.sh` outside the public web root.
2. Make the script executable.
3. Run it from the WordPress installation directory, where WP-CLI can locate
   the website.

```bash
chmod 0755 envato-market-update.sh
./envato-market-update.sh
```

## Script

```bash
#!/usr/bin/env bash

set -eu

WP_CONTENT_DIR="$(wp eval 'echo WP_CONTENT_DIR;')"

if [ -z "${WP_CONTENT_DIR}" ] || [ ! -d "${WP_CONTENT_DIR}" ]; then
    echo "WordPress content directory was not found." >&2
    exit 1
fi

wp plugin install \
    "https://github.com/envato/wp-envato-market/archive/master.zip" \
    --force
```
