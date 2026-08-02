<?php

/*
 * Resize oversized original media files.
 *
 * This file is loadable with WP-CLI's --require flag:
 *
 * wp --require=resize-original-media.php media resize-originals
 *
 */

namespace SzepeViktor\WordPress\Cli;

use WP_CLI;

final class ResizeOriginalMedia
{
    private const MAX_DIMENSION = 1600;
    private const QUALITY = 91;
    private const BATCH_SIZE = 10;

    /**
     * Resize original JPEG, PNG, and WebP uploads exceeding the maximum size.
     *
     * ## EXAMPLES
     *
     *     wp --require=tools/resize-original-media.php media resize-originals
     *     wp --require=tools/resize-original-media.php media resize-originals --dry-run
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Estimate the disk gain without changing files or metadata.
     *
     * @when after_wp_load
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        if (!class_exists('Imagick')) {
            WP_CLI::warning('The Imagick PHP extension is not available; WordPress may use another image editor.');
        }

        add_filter(
            'image_save_progressive',
            static function (bool $progressive, string $mime_type): bool {
                return $progressive || 'image/jpeg' === $mime_type;
            },
            10,
            2
        );

        $dry_run = isset($assoc_args['dry-run']);
        $backup_count = 0;
        $candidate_count = 0;
        $estimated_gain = 0;

        for ($page = 1; ; $page++) {
            $attachment_ids = get_posts(
                [
                    'post_type' => 'attachment',
                    'post_status' => 'inherit',
                    'post_mime_type' => ['image/jpeg', 'image/png', 'image/webp'],
                    'posts_per_page' => self::BATCH_SIZE,
                    'paged' => $page,
                    'fields' => 'ids',
                    'orderby' => 'ID',
                    'order' => 'ASC',
                ]
            );

            if (!$attachment_ids) {
                break;
            }

            foreach ($attachment_ids as $attachment_id) {
                $metadata = wp_get_attachment_metadata($attachment_id);
                if (!is_array($metadata)) {
                    WP_CLI::warning(
                        sprintf('Unable to read metadata for attachment %d; skipping', $attachment_id)
                    );
                    continue;
                }

                $has_retained_original = !empty($metadata['original_image']);
                $path = $has_retained_original
                    ? wp_get_original_image_path($attachment_id)
                    : get_attached_file($attachment_id);
                if (!is_string($path) || !is_file($path)) {
                    WP_CLI::warning(
                        sprintf('Unable to locate original file for attachment %d; skipping', $attachment_id)
                    );
                    continue;
                }

                $size = @getimagesize($path);
                if (!is_array($size) || !isset($size[0], $size[1])) {
                    WP_CLI::warning(
                        sprintf('Unable to read dimensions for attachment %d: %s', $attachment_id, $path)
                    );
                    continue;
                }

                $width = (int) $size[0];
                $height = (int) $size[1];
                if ($width <= self::MAX_DIMENSION && $height <= self::MAX_DIMENSION) {
                    continue;
                }

                $file_size = filesize($path);
                if (!is_int($file_size)) {
                    WP_CLI::warning(
                        sprintf('Unable to read file size for attachment %d: %s', $attachment_id, $path)
                    );
                    continue;
                }

                $scale = min(1, self::MAX_DIMENSION / $width, self::MAX_DIMENSION / $height);
                $new_width = max(1, (int) round($width * $scale));
                $new_height = max(1, (int) round($height * $scale));
                $estimated_size = (int) round(
                    $file_size * ($new_width * $new_height) / ($width * $height)
                );
                $estimated_gain += max(0, $file_size - $estimated_size);
                $candidate_count++;

                if ($dry_run) {
                    continue;
                }

                $backup_path = $path.'~';
                if (!is_file($backup_path) && !copy($path, $backup_path)) {
                    WP_CLI::warning(
                        sprintf('Unable to create backup for attachment %d: %s', $attachment_id, $backup_path)
                    );
                    continue;
                }
                $backup_count++;

                $editor = wp_get_image_editor($path);
                if (is_wp_error($editor)) {
                    WP_CLI::warning(
                        sprintf(
                            'Unable to open attachment %d: %s',
                            $attachment_id,
                            $editor->get_error_message()
                        )
                    );
                    continue;
                }

                $rotation = $editor->maybe_exif_rotate();
                if (is_wp_error($rotation)) {
                    WP_CLI::warning(
                        sprintf(
                            'Unable to apply EXIF orientation for attachment %d: %s',
                            $attachment_id,
                            $rotation->get_error_message()
                        )
                    );
                    continue;
                }

                $resize = $editor->resize(self::MAX_DIMENSION, self::MAX_DIMENSION, true);
                if (is_wp_error($resize)) {
                    WP_CLI::warning(
                        sprintf(
                            'Unable to resize attachment %d: %s',
                            $attachment_id,
                            $resize->get_error_message()
                        )
                    );
                    continue;
                }

                if (in_array(get_post_mime_type($attachment_id), ['image/jpeg', 'image/webp'], true)) {
                    $editor->set_quality(self::QUALITY);
                }

                $saved = $editor->save($path);
                if (is_wp_error($saved)) {
                    WP_CLI::warning(
                        sprintf(
                            'Unable to save attachment %d: %s',
                            $attachment_id,
                            $saved->get_error_message()
                        )
                    );
                    continue;
                }

                $final_size = @getimagesize($path);
                if (!is_array($final_size) || !isset($final_size[0], $final_size[1])) {
                    WP_CLI::warning(
                        sprintf(
                            'Unable to read resized dimensions for attachment %d: %s',
                            $attachment_id,
                            $path
                        )
                    );
                    continue;
                }

                // Store the original file's dimensions without regenerating sub-sizes.
                $metadata['original_width'] = (int) $final_size[0];
                $metadata['original_height'] = (int) $final_size[1];
                if (false === wp_update_attachment_metadata($attachment_id, $metadata)) {
                    WP_CLI::warning(
                        sprintf(
                            'Unable to update metadata for attachment %d; keeping %s',
                            $attachment_id,
                            $backup_path
                        )
                    );
                    continue;
                }

                WP_CLI::log(sprintf('%d: %s (%d x %d)', $attachment_id, $path, $width, $height));
            }

            if (count($attachment_ids) < self::BATCH_SIZE) {
                break;
            }
        }

        if ($dry_run) {
            WP_CLI::success(
                sprintf(
                    'Estimated total disk gain: %s across %d original uploads. This is an approximation.',
                    size_format($estimated_gain, 2),
                    $candidate_count
                )
            );
            return;
        }

        if ($backup_count > 0) {
            $uploads_dir = wp_upload_dir()['basedir'];
            $delete_command = sprintf(
                "find %s -type f -name '*~' -delete",
                escapeshellarg($uploads_dir)
            );
            WP_CLI::warning(sprintf('Backups were kept. Delete them all with: %s', $delete_command));
        }
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('media resize-originals', ResizeOriginalMedia::class);
}
