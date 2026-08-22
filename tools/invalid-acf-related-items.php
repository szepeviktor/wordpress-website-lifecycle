<?php

declare(strict_types=1);

/*
 * Find ACF relationship fields pointing to missing or non-published posts.
 *
 * This file is loadable with WP-CLI's --require flag:
 *
 * wp --require=invalid-acf-related-items.php acf invalid-related-items <meta-key-suffix>
 */

namespace SzepeViktor\WordPress\Cli;

use WP_CLI;

use function WP_CLI\Utils\format_items;

final class InvalidAcfRelatedItems
{
    /**
     * Finds ACF relationship field values pointing to unavailable posts.
     *
     * The command scans published source posts for post meta keys ending with
     * the given suffix. ACF field definition meta keys starting with `_` are
     * ignored.
     *
     * ## OPTIONS
     *
     * <meta-key-suffix>
     * : Meta key suffix to inspect.
     *
     * [--format=<format>]
     * : Render results in a particular format.
     * ---
     * default: table
     * options:
     *   - plain
     *   - table
     *   - csv
     *   - json
     *   - yaml
     *   - count
     * ---
     *
     * ## EXAMPLES
     *
     *     $ wp acf invalid-related-items related_items
     *     +-----------+---------------+------------+---------+------+-------+
     *     | source_id | meta_key      | related_id | status  | type | title |
     *     +-----------+---------------+------------+---------+------+-------+
     *     | 42        | related_items | 123        | missing |      |       |
     *     +-----------+---------------+------------+---------+------+-------+
     *
     *     $ wp acf invalid-related-items featured_posts --format=count
     *     1
     *
     * @when after_wp_load
     *
     * @param array<int, string> $args
     * @param array<string, mixed> $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        $meta_key_suffix = $args[0] ?? null;

        if (!is_string($meta_key_suffix) || '' === $meta_key_suffix) {
            WP_CLI::error('Meta key suffix must be a non-empty string.');
        }

        $rows = $this->getRelationshipRows($meta_key_suffix);
        $findings = [];

        foreach ($rows as $row) {
            if (
                !isset($row->post_id, $row->meta_key, $row->meta_value)
                || !is_numeric($row->post_id)
                || !is_string($row->meta_key)
                || !is_string($row->meta_value)
            ) {
                WP_CLI::error('WordPress returned a malformed post meta row.');
            }

            $items = maybe_unserialize($row->meta_value);

            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $related_id) {
                $related_id = (int) $related_id;

                if (0 === $related_id) {
                    continue;
                }

                $post = get_post($related_id);

                if (null === $post) {
                    $findings[] = [
                        'source_id' => (int) $row->post_id,
                        'meta_key' => $row->meta_key,
                        'related_id' => $related_id,
                        'status' => 'missing',
                        'type' => '',
                        'title' => '',
                    ];
                    continue;
                }

                if ('publish' === $post->post_status) {
                    continue;
                }

                $findings[] = [
                    'source_id' => (int) $row->post_id,
                    'meta_key' => $row->meta_key,
                    'related_id' => $related_id,
                    'status' => $post->post_status,
                    'type' => $post->post_type,
                    'title' => $post->post_title,
                ];
            }
        }

        usort(
            $findings,
            static fn (array $left, array $right): int => $left['source_id'] <=> $right['source_id']
                ?: strnatcasecmp($left['meta_key'], $right['meta_key'])
                ?: $left['related_id'] <=> $right['related_id']
        );

        $fields = ['source_id', 'meta_key', 'related_id', 'status', 'type', 'title'];
        $format = $assoc_args['format'] ?? 'table';

        if ('plain' === $format) {
            foreach ($findings as $finding) {
                WP_CLI::line(sprintf(
                    '%d:%s:%d:%s:%s:%s',
                    $finding['source_id'],
                    $finding['meta_key'],
                    $finding['related_id'],
                    $finding['status'],
                    $finding['type'],
                    $finding['title']
                ));
            }
        } else {
            format_items($format, $findings, $fields);
        }

        if ([] !== $findings) {
            WP_CLI::halt(1);
        }
    }

    /**
     * @return array<int, object>
     */
    private function getRelationshipRows(string $meta_key_suffix): array
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT pm.post_id, pm.meta_key, pm.meta_value
                FROM %i pm
                INNER JOIN %i p ON p.ID = pm.post_id
                WHERE pm.meta_key LIKE %s
                  AND pm.meta_key NOT LIKE '\\_%%'
                  AND p.post_status = 'publish'
                ",
                $wpdb->postmeta,
                $wpdb->posts,
                '%' . $wpdb->esc_like($meta_key_suffix)
            )
        );

        if (!is_array($rows)) {
            WP_CLI::error('WordPress returned an invalid post meta result.');
        }

        return $rows;
    }
}

WP_CLI::add_command('acf invalid-related-items', InvalidAcfRelatedItems::class);
