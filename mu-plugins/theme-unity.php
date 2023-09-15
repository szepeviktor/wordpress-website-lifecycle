<?php

/*
 * Unity theme settings
 */

$unity_theme_update = <<<'EOF'
#!/bin/bash

CURRENT="$(dirname "$0")/external-plugin-update.log"

# From wp-content/themes/unity/inc/tgm-plugins.php
EXTERNAL_PLUGINS=(
    "http://source.wpopal.com/plugins/new/js_composer.zip"
    "http://source.wpopal.com/plugins/new/revslider.zip"
)

for PLUGIN in "${EXTERNAL_PLUGINS[@]}"; do
    wget -q --spider -S "$PLUGIN" 2>&1 | grep -F 'Last-Modified:'
done | diff "$CURRENT" -

#exit 0
EOF;

file_put_contents('unity-plugin-update.sh', $unity_theme_update);
chmod('unity-plugin-update.sh', 0755);
