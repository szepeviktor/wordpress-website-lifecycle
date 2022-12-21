#!/bin/bash
#
# Moving a site to a subdirectory
#

SUBDIR="project"
URL="$(wp option get home)"

# Change 'siteurl'
wp option set siteurl "${URL}/${SUBDIR}"

# Change URL in database
wp search-replace --precise --recurse-objects --all-tables-with-prefix "/wp-includes/" "/${SUBDIR}/wp-includes/"

# Change constants in wp-config.php
# - WP_CONTENT_DIR
# - WP_CONTENT_URL
# - TINY_CDN_INCLUDES_URL
# - TINY_CDN_CONTENT_URL
editor wp-config.php

# Move core to subdir
xargs -I % mv -v ./% ./${SUBDIR}/ <<"EOF"
wp-admin
wp-includes
licenc.txt
license.txt
olvasdel.html
readme.html
wp-activate.php
wp-blog-header.php
wp-comments-post.php
wp-config-sample.php
wp-cron.php
wp-links-opml.php
wp-load.php
wp-login.php
wp-mail.php
wp-settings.php
wp-signup.php
wp-trackback.php
xmlrpc.php
EOF
cp -v ./index.php ./${SUBDIR}/

# Modify /index.php
sed -e "s|'/wp-blog-header\\.php'|'/${SUBDIR}/wp-blog-header.php'|" -i ./index.php

# Move files from parent directory
mv -v ../wp-config.php ./
mv -v ../waf4wordpress-http-analyzer.php ./

# Edit "path:" in wp-cli.yml
editor ../wp-cli.yml

# Fix Apache VirtualHost configuration

# Flush cache
wp cache flush
