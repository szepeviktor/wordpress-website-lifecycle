# Unity theme bundled plugin monitor

Use this script to detect new releases of the WPBakery and Revolution Slider
archives bundled by the Unity theme. This is an operations script, not an MU
plugin; do not place it in `wp-content/mu-plugins/`.

## Instructions

1. Save the snippet as `unity-plugin-update.sh` outside the public web root.
2. Make the script executable.
3. Create the initial `external-plugin-update.log` baseline.
4. Run the script without arguments from a scheduled job. It exits with the
   status returned by `diff` and prints any changed HTTP metadata.
5. After reviewing and deploying an update, refresh the baseline.

```bash
chmod 0755 unity-plugin-update.sh
./unity-plugin-update.sh --update
./unity-plugin-update.sh
```

## Script

```bash
#!/usr/bin/env bash

set -eu

SCRIPT_DIR="$(CDPATH='' cd -- "$(dirname -- "$0")" && pwd)"
CURRENT="${SCRIPT_DIR}/external-plugin-update.log"
REMOTE="$(mktemp)"

trap 'rm -f "${REMOTE}"' EXIT

EXTERNAL_PLUGINS=(
    "https://source.wpopal.com/plugins/new/js_composer.zip"
    "https://source.wpopal.com/plugins/new/revslider.zip"
)

for PLUGIN in "${EXTERNAL_PLUGINS[@]}"; do
    curl -L -sI "${PLUGIN}" \
        | sed -n '/^last-modified:/Ip'
done > "${REMOTE}"

if [ "${1:-}" = "--update" ]; then
    cp "${REMOTE}" "${CURRENT}"
    exit 0
fi

if [ ! -f "${CURRENT}" ]; then
    echo "Baseline is missing. Run $0 --update first." >&2
    exit 2
fi

diff -u "${CURRENT}" "${REMOTE}"
```
