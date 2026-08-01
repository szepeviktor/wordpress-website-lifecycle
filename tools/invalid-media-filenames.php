<?php

declare(strict_types=1);

/*
 * Find files in the WordPress uploads directory with unsafe characters.
 *
 * This file is loadable with WP-CLI's --require flag:
 *
 * wp --require=invalid-media-filenames.php media invalid-filenames
 */

namespace SzepeViktor\WordPress\Cli;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use WP_CLI;

use function WP_CLI\Utils\format_items;

final class InvalidMediaFilenames
{
    /**
     * Finds media filenames containing unsafe characters.
     *
     * A valid filename may contain ASCII letters, digits, dots, underscores,
     * and hyphens. Directory names are not checked.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Render results in a particular format.
     * ---
     * default: plain
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
     *     $ wp media invalid-filenames
     *     2026/07/customer photo.jpg
     *
     *     $ wp media invalid-filenames --format=json
     *     [{"file":"2026/07/customer photo.jpg"}]
     *
     * @when after_wp_load
     *
     * @param array<int, string> $args
     * @param array<string, mixed> $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        $upload_dir = wp_upload_dir(null, false);

        if (!empty($upload_dir['error'])) {
            WP_CLI::error(sprintf('Unable to locate the uploads directory: %s', $upload_dir['error']));
        }

        if (!isset($upload_dir['basedir']) || !is_string($upload_dir['basedir'])) {
            WP_CLI::error('WordPress returned an invalid uploads directory.');
        }

        $base_dir = wp_normalize_path($upload_dir['basedir']);

        if (!is_dir($base_dir)) {
            WP_CLI::error(sprintf('Uploads directory does not exist: %s', $base_dir));
        }

        if (!is_readable($base_dir)) {
            WP_CLI::error(sprintf('Uploads directory is not readable: %s', $base_dir));
        }

        $findings = [];

        try {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $base_dir,
                    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
                )
            );

            foreach ($files as $file) {
                if (!$file->isFile() || $file->isLink()) {
                    continue;
                }

                if (1 !== preg_match('/[^0-9A-Za-z._-]/', $file->getBasename())) {
                    continue;
                }

                $path = wp_normalize_path($file->getPathname());
                $findings[] = [
                    'file' => ltrim(substr($path, strlen($base_dir)), '/'),
                ];
            }
        } catch (Throwable $throwable) {
            WP_CLI::error(sprintf('Unable to inspect the uploads directory: %s', $throwable->getMessage()));
        }

        usort(
            $findings,
            static fn (array $left, array $right): int => strnatcasecmp($left['file'], $right['file'])
        );

        $format = $assoc_args['format'] ?? 'plain';

        if ('plain' === $format) {
            foreach ($findings as $finding) {
                WP_CLI::line($finding['file']);
            }
        } else {
            format_items($format, $findings, ['file']);
        }

        if ([] !== $findings) {
            WP_CLI::halt(1);
        }
    }
}

WP_CLI::add_command('media invalid-filenames', InvalidMediaFilenames::class);
