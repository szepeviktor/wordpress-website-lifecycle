<?php

declare(strict_types=1);

/*
 * Print new rows from an append-only database table as JSON lines.
 *
 * This file is loadable with WP-CLI's --require flag:
 *
 * wp --require=db-tail.php db tail wpmailsmtp_debug_events --cursor=id
 */

namespace SzepeViktor\WordPress\Cli;

use JsonException;
use WP_CLI;

final class DatabaseTail
{
    /**
     * Prints new rows from an append-only database table as JSON lines.
     *
     * ## OPTIONS
     *
     * <table>
     * : Table name without the WordPress table prefix.
     *
     * --cursor=<column>
     * : Unique, nonnegative, increasing integer column (usually id).
     *
     * [--columns=<columns>]
     * : Comma-separated output columns. Defaults to all columns.
     *
     * [--where=<expression>]
     * : Trusted SQL WHERE expression, without the WHERE keyword.
     *
     * ## EXAMPLES
     *
     *     wp db tail wpmailsmtp_debug_events --cursor=id --columns=id,created_at,content
     *
     * @when after_wp_load
     *
     * @param array<int, string> $args
     * @param array<string, mixed> $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        global $wpdb;

        $table = sprintf('%s%s', $wpdb->prefix, $args[0]);
        $cursor = $assoc_args['cursor'];
        $columns = isset($assoc_args['columns']) ? array_map('trim', explode(',', $assoc_args['columns'])) : [];
        $where = trim($assoc_args['where'] ?? '1=1');

        foreach (array_merge([$table, $cursor], $columns) as $identifier) {
            if (preg_match('/\A[a-zA-Z0-9_]+\z/', $identifier) !== 1) {
                WP_CLI::error(sprintf(
                    'Invalid SQL identifier: %s. Use only ASCII letters, digits, and underscores.',
                    $identifier
                ));
            }
        }

        $identity = [
            'database' => DB_NAME,
            'table' => $table,
            'cursor' => $cursor,
            'columns' => $columns,
            'where' => $where,
        ];
        try {
            $option = sprintf('db_tail_%s', hash('sha256', json_encode($identity, JSON_THROW_ON_ERROR)));
        } catch (JsonException $exception) {
            WP_CLI::error(sprintf('Unable to encode query identity as JSON: %s', $exception->getMessage()));
        }
        // Read fresh state, bypassing persistent caches.
        wp_cache_delete($option, 'options');
        wp_cache_delete('notoptions', 'options');
        $last = get_option($option, null);
        $last = $last === null ? null : (string) $last;

        $selection = $columns === [] ? '*' : implode(', ', array_map(
            static function (string $column): string {
                return sprintf('`%s`', $column);
            },
            array_unique(array_merge($columns, [$cursor]))
        ));
        while (true) {
            $range = $last === null ? '' : $wpdb->prepare(sprintf(' AND `%s` > %%s', $cursor), $last);
            $sql = sprintf(
                'SELECT %s FROM `%s` WHERE (%s)%s ORDER BY `%s` ASC LIMIT 1000',
                $selection,
                $table,
                $where,
                $range,
                $cursor
            );
            $rows = $wpdb->get_results($sql, ARRAY_A);
            self::checkDatabase();
            if ($rows === []) {
                break;
            }
            foreach ($rows as $row) {
                $next = (string) $row[$cursor];
                if ($columns !== [] && !in_array($cursor, $columns, true)) {
                    unset($row[$cursor]);
                }
                try {
                    $json = json_encode(
                        $row,
                        JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                } catch (JsonException $exception) {
                    WP_CLI::error(sprintf('Unable to encode database row as JSON: %s', $exception->getMessage()));
                }
                WP_CLI::line($json);
                $last = $next;
            }
            if (!update_option($option, $last, false)) {
                WP_CLI::error(sprintf('Failed to save state in option %s.', $option));
            }
        }
    }

    private static function checkDatabase(): void
    {
        global $wpdb;
        if ($wpdb->last_error !== '') {
            WP_CLI::error($wpdb->last_error);
        }
    }
}

WP_CLI::add_command('db tail', DatabaseTail::class);
