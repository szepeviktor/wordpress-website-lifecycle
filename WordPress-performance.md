# WordPress performance

How to achieve high performance in WordPress?

| Item                          | Tool                               | Speedup                       |
| ----------------------------- | ---------------------------------- | ----------------------------- |
| [Infrastructure](/WordPress-stack.md) | CPU, disk, web server, PHP ([OPcache](http://php.net/manual/en/opcache.configuration.php#ini.opcache.validate-timestamps)) and DNS | Overall performance |
| In-memory object cache        | Redis, Memcached, APCu             | options, post, post meta etc. |
| Plugins for administration<br>(backup, DB cleanup) | Use WP-CLI instead | **Degrades** performance |
| Theme and plugins             | Cache-aware ones using object cache or transients |                |
| Translations                  | `tiny-translation-cache`           | .mo file parsing              |
| Navigation menus              | `tiny-nav-menu-cache`              | `wp_nav_menu()`               |
| Post content                  | `tiny-cache`                       | `the_content()`               |
| Template output               | `tiny-cache`                       | `get_template_part()`         |
| Widgets                       | `widget-output-cache` plugin       | `dynamic_sidebar()`           |

This list concentrates on WordPress core generating HTML code.
Frontend loading (full page cache) is another topic.
