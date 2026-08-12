<?php

declare(strict_types=1);

/*
 * Delete media library attachments whose original file is missing from disk.
 *
 * This file is loadable with WP-CLI's --require flag:
 *
 * wp --require=missing-media-files.php media missing-files
 */

namespace SzepeViktor\WordPress\Cli;

use WP_CLI;

use function WP_CLI\Utils\format_items;

final class MissingMediaFiles
{
    /**
     * Deletes attachment posts whose attached file no longer exists on disk.
     *
     * The command checks the attachment's `_wp_attached_file` metadata beneath
     * the WordPress uploads directory. Use `--dry-run` to inspect affected
     * attachments without deleting them.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : List missing media attachments without deleting their posts.
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
     *     $ wp media missing-files --dry-run
     *     +----+--------------------------+---------+
     *     | id | file                     | deleted |
     *     +----+--------------------------+---------+
     *     | 42 | 2026/07/missing-file.jpg | no      |
     *     +----+--------------------------+---------+
     *
     *     $ wp media missing-files
     *     Success: Deleted 1 attachment with missing files.
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

        $dry_run = isset($assoc_args['dry-run']);
        $findings = [];

        foreach ($this->getAttachmentIds() as $attachment_id) {
            $relative_file = get_post_meta($attachment_id, '_wp_attached_file', true);

            if (!is_string($relative_file) || '' === $relative_file) {
                continue;
            }

            $absolute_file = wp_normalize_path($base_dir . '/' . ltrim($relative_file, '/'));

            if (is_file($absolute_file)) {
                continue;
            }

            $findings[] = [
                'id' => $attachment_id,
                'file' => $relative_file,
                'deleted' => 'no',
            ];
        }

        if (!$dry_run) {
            foreach ($findings as $index => $finding) {
                $deleted = wp_delete_attachment($finding['id'], true);

                if (false === $deleted || null === $deleted) {
                    WP_CLI::warning(sprintf(
                        'Unable to delete attachment %d: %s',
                        $finding['id'],
                        $finding['file']
                    ));
                    continue;
                }

                $findings[$index]['deleted'] = 'yes';
            }
        }

        usort(
            $findings,
            static fn (array $left, array $right): int => $left['id'] <=> $right['id']
        );

        $format = $assoc_args['format'] ?? 'table';

        if ('plain' === $format) {
            foreach ($findings as $finding) {
                WP_CLI::line($finding['file']);
            }
        } else {
            format_items($format, $findings, ['id', 'file', 'deleted']);
        }

        if ($dry_run) {
            if ([] !== $findings) {
                WP_CLI::halt(1);
            }

            return;
        }

        $deleted_count = count(array_filter(
            $findings,
            static fn (array $finding): bool => 'yes' === $finding['deleted']
        ));
        $failed_count = count($findings) - $deleted_count;

        if ($failed_count > 0) {
            WP_CLI::error(sprintf(
                'Deleted %d attachments with missing files; failed to delete %d.',
                $deleted_count,
                $failed_count
            ));
        }

        WP_CLI::success(sprintf(
            'Deleted %d %s with missing files.',
            $deleted_count,
            1 === $deleted_count ? 'attachment' : 'attachments'
        ));
    }

    /**
     * @return array<int, int>
     */
    private function getAttachmentIds(): array
    {
        $attachment_ids = [];

        for ($page = 1; ; $page++) {
            $page_ids = get_posts(
                [
                    'post_type' => 'attachment',
                    'post_status' => 'inherit',
                    'posts_per_page' => 500,
                    'paged' => $page,
                    'fields' => 'ids',
                    'orderby' => 'ID',
                    'order' => 'ASC',
                ]
            );

            if (!$page_ids) {
                break;
            }

            foreach ($page_ids as $attachment_id) {
                $attachment_ids[] = (int) $attachment_id;
            }

            if (count($page_ids) < 500) {
                break;
            }
        }

        return $attachment_ids;
    }
}

WP_CLI::add_command('media missing-files', MissingMediaFiles::class);
