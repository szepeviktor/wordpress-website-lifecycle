<?php

declare(strict_types=1);

/*
 * Find scheduled WP-Cron events without registered action callbacks.
 *
 * This file is loadable with WP-CLI's --require flag:
 *
 * wp --require=missing-cron-hooks.php cron missing-hooks
 */

namespace SzepeViktor\WordPress\Cli;

use WP_CLI;

use function WP_CLI\Utils\format_items;

final class MissingCronHooks
{
    /**
     * Finds scheduled WP-Cron hooks without registered callbacks.
     *
     * The result reflects callbacks registered in the current WP-CLI context.
     *
     * ## OPTIONS
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
     *     $ wp cron missing-hooks
     *     +--------------------------+--------+---------------------+
     *     | hook                     | events | next_run_gmt        |
     *     +--------------------------+--------+---------------------+
     *     | abandoned_cleanup        | 3      | 2026-08-02 01:30:00 |
     *     +--------------------------+--------+---------------------+
     *
     *     $ wp cron missing-hooks --format=count
     *     1
     *
     * @when after_wp_load
     *
     * @param array<int, string> $args
     * @param array<string, mixed> $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        $crons = _get_cron_array();

        if (!is_array($crons)) {
            WP_CLI::error('WordPress returned an invalid cron event array.');
        }

        $findings = [];

        foreach ($crons as $timestamp => $hooks) {
            if (!is_numeric($timestamp) || !is_array($hooks)) {
                WP_CLI::error('WordPress returned a malformed cron event array.');
            }

            $event_timestamp = (int) $timestamp;

            foreach ($hooks as $hook => $events) {
                if (!is_string($hook) || !is_array($events)) {
                    WP_CLI::error('WordPress returned a malformed cron event.');
                }

                if (false !== has_action($hook)) {
                    continue;
                }

                if (!isset($findings[$hook])) {
                    $findings[$hook] = [
                        'hook' => $hook,
                        'events' => 0,
                        'next_run' => $event_timestamp,
                    ];
                }

                $findings[$hook]['events'] += count($events);
                $findings[$hook]['next_run'] = min($findings[$hook]['next_run'], $event_timestamp);
            }
        }

        usort(
            $findings,
            static function (array $left, array $right): int {
                return $left['next_run'] <=> $right['next_run']
                    ?: strnatcasecmp($left['hook'], $right['hook']);
            }
        );

        $items = array_map(
            static fn (array $finding): array => [
                'hook' => $finding['hook'],
                'events' => $finding['events'],
                'next_run_gmt' => gmdate('Y-m-d H:i:s', $finding['next_run']),
            ],
            $findings
        );

        $format = $assoc_args['format'] ?? 'table';

        if ('plain' === $format) {
            foreach ($items as $item) {
                WP_CLI::line($item['hook']);
            }
            return;
        }

        format_items($format, $items, ['hook', 'events', 'next_run_gmt']);
    }
}

WP_CLI::add_command('cron missing-hooks', MissingCronHooks::class);
