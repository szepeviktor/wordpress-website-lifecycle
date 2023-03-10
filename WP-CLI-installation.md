# Installation with WP-CLI

`wp-cli.yml`

```yaml
path: WP-ROOT
url: WP-HOME-URL
debug: true
user: viktor
core update:
    locale: hu_HU
skip-plugins:
    # Version randomizer
    - better-wp-security
```

WP-CLI commands.

```bash
# Existing database
./wp-createdb.sh

# New installation
wp core download --locale=hu_HU
wp core config --dbname="$DBNAME" --dbuser="$DBUSER" --dbpass="$DBPASS" \
    --dbhost="$DBHOST" --dbprefix="prod" --dbcharset="$DBCHARSET" --extra-php <<EOF
// Extra PHP code
EOF
wp core install --title="WP" --admin_user="viktor" --admin_email="viktor@szepe.net" --admin_password="12345"

wp option set home "WP-HOME-URL"
wp option set blog_public 0
wp option set admin_email "webmaster@example.com"
```
