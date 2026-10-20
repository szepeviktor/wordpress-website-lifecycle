<?php

declare(strict_types=1);

/*
 * Audit attachment metadata, cache consistency, and image files without changes.
 *
 * This file is loadable with WP-CLI's --require flag:
 *
 * wp --require=attachment-size-audit.php media audit-sizes
 */

namespace SzepeViktor\WordPress\Cli;

use WP_CLI;

use function WP_CLI\Utils\format_items;

final class AttachmentSizeAudit
{
    // Keep the correct metadata row and remove duplicate rows for the same attachment and key.
    private const DB_DUPLICATE_META = 'DB_DUPLICATE_META';
    // Restore _wp_attached_file with the correct upload-relative path or reimport the image.
    private const DB_ATTACHED_FILE_MISSING = 'DB_ATTACHED_FILE_MISSING';
    // Restore the attached file path first, then regenerate attachment metadata with wp media regenerate <id>.
    private const DB_IMAGE_METADATA_MISSING = 'DB_IMAGE_METADATA_MISSING';
    // Rebuild corrupt attachment metadata with wp media regenerate <id> after verifying the source file.
    private const DB_IMAGE_METADATA_INVALID = 'DB_IMAGE_METADATA_INVALID';
    // Invalidate the attachment post_meta object-cache entry after confirming the database path is correct.
    private const CACHE_ATTACHED_FILE_MISMATCH = 'CACHE_ATTACHED_FILE_MISMATCH';
    // Invalidate the attachment post_meta object-cache entry after confirming the database metadata is correct.
    private const CACHE_IMAGE_METADATA_MISMATCH = 'CACHE_IMAGE_METADATA_MISMATCH';
    private const OK = 'OK';
    // Restore the main file from backup, or regenerate a missing intermediate size with wp media regenerate <id>.
    private const FILE_MISSING = 'FILE_MISSING';
    // Restore metadata[file] to the correct upload-relative main image path, then regenerate metadata.
    private const DB_METADATA_FILE_MISSING = 'DB_METADATA_FILE_MISSING';
    // Restore the referenced main image from backup or correct metadata[file] to the existing main image.
    private const METADATA_FILE_MISSING = 'METADATA_FILE_MISSING';
    // Reconcile _wp_attached_file and metadata[file] so both reference the correct main image.
    private const DB_FILE_PATH_MISMATCH = 'DB_FILE_PATH_MISMATCH';
    // Restore or re-encode a damaged image, or enable PHP support for its format, then regenerate metadata.
    private const FILE_UNREADABLE_IMAGE = 'FILE_UNREADABLE_IMAGE';
    // Verify the image file is correct, then rebuild its stored dimensions with wp media regenerate <id>.
    private const DB_DIMENSIONS_MISMATCH = 'DB_DIMENSIONS_MISMATCH';
    // Restore the retained original from backup, or remove the stale original_image reference if intentionally deleted.
    private const ORIGINAL_FILE_MISSING = 'ORIGINAL_FILE_MISSING';
    // Repair attachment paths and metadata, invalidate stale postmeta cache, and check image-source filters.
    private const WP_FULL_SRC_FALSE = 'WP_FULL_SRC_FALSE';
    // Confirm the size is still required, then generate missing sizes with wp media regenerate <id> --only-missing.
    private const DB_SIZE_MISSING = 'DB_SIZE_MISSING';
    // Regenerate the affected size with wp media regenerate <id> --image_size=<size> after verifying the main image.
    private const DB_SIZE_FILE_MISSING = 'DB_SIZE_FILE_MISSING';

    /**
     * @var list<array{
     *     attachment_id: int,
     *     size: string,
     *     status: string,
     *     db_value: string,
     *     file_path: string,
     *     detail: string
     * }>
     */
    private array $findings = [];

    private bool $includeOk = false;

    private int $issueCount = 0;

    /**
     * Audits image attachment metadata, cached values, files, and dimensions.
     *
     * By default, checks full images and WordPress core default sizes, plus
     * WooCommerce core default sizes when WooCommerce is active.
     * Compares raw database metadata with WordPress postmeta and checks stored
     * dimensions against actual files. Does not modify files, metadata, or caches.
     *
     * ## OPTIONS
     *
     * [<id>]
     * : Check a single image attachment.
     *
     * [--all-sizes]
     * : Check all currently registered and database-stored image sizes.
     *
     * [--include-ok]
     * : Include successful file checks in the output.
     *
     * [--format=<format>]
     * : Render results in a particular format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - yaml
     *   - count
     * ---
     *
     * ## EXAMPLES
     *
     *     $ wp media audit-sizes
     *     $ wp media audit-sizes 123 --format=json
     *     $ wp media audit-sizes --all-sizes --include-ok --format=csv
     *
     * @when after_wp_load
     *
     * @param array<int, string> $args
     * @param array<string, mixed> $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        global $wpdb;

        $all_sizes = isset($assoc_args['all-sizes']);
        $this->findings = [];
        $this->issueCount = 0;
        $this->includeOk = isset($assoc_args['include-ok']);
        $single_id = 0;
        $batch_size = 200;

        if (count($args) > 1) {
            WP_CLI::error('Provide at most one image attachment ID.');
        }

        if (isset($args[0])) {
            $value = (string) $args[0];
            if (!ctype_digit($value) || (int) $value < 1 || (string) (int) $value !== $value) {
                WP_CLI::error('The attachment ID must be a positive integer.');
            }
            $single_id = (int) $value;
        }

        $upload_info = wp_get_upload_dir();
        $uploads_dir = isset($upload_info['basedir']) ? rtrim($upload_info['basedir'], '/\\') : '';
        if ($uploads_dir === '' || !empty($upload_info['error'])) {
            WP_CLI::error('Upload directory not available: ' . print_r($upload_info['error'] ?? '', true));
        }

        $standard_names = [
            'thumbnail',
            'medium',
            'medium_large',
            'large',
            '1536x1536',
            '2048x2048',
        ];

        if (class_exists('WooCommerce')) {
            $standard_names = array_merge($standard_names, [
                'woocommerce_thumbnail',
                'woocommerce_single',
                'woocommerce_gallery_thumbnail',
            ]);
        }

        $registered = wp_get_registered_image_subsizes();

        $checked_attachments = 0;
        $last_id = 0;

        while (true) {
            if ($single_id > 0) {
                if ($last_id > 0) {
                    break;
                }
                $sql = $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_type = %s AND post_mime_type LIKE %s",
                    $single_id,
                    'attachment',
                    'image/%'
                );
            } else {
                $sql = $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE ID > %d AND post_type = %s AND post_mime_type LIKE %s"
                    . " ORDER BY ID ASC LIMIT %d",
                    $last_id,
                    'attachment',
                    'image/%',
                    $batch_size
                );
            }

            $ids = array_map('intval', $wpdb->get_col($sql));
            if ($wpdb->last_error) {
                WP_CLI::error('Cannot fetch attachment IDs: ' . $wpdb->last_error);
            }
            if (!$ids) {
                break;
            }
            $last_id = (int) end($ids);

            // Bypass WP object cache and read attachment metadata DIRECTLY from DB.
            $safe_ids = implode(',', $ids);
            $meta_sql = $wpdb->prepare(
                "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta}"
                . " WHERE post_id IN ({$safe_ids}) AND meta_key IN (%s, %s) ORDER BY meta_id ASC",
                '_wp_attached_file',
                '_wp_attachment_metadata'
            );
            $rows = $wpdb->get_results($meta_sql, ARRAY_A);
            if ($wpdb->last_error) {
                WP_CLI::error('Cannot fetch raw DB metadata: ' . $wpdb->last_error);
            }
            $db = [];
            $key_counts = [];
            foreach ($rows as $row) {
                $pid = (int) $row['post_id'];
                $key = $row['meta_key'];
                $db[$pid][$key] = $row['meta_value'];
                $key_counts[$pid][$key] = ($key_counts[$pid][$key] ?? 0) + 1;
            }

            foreach ($ids as $id) {
                $checked_attachments++;
                $raw_file = $db[$id]['_wp_attached_file'] ?? '';
                $raw_meta = $db[$id]['_wp_attachment_metadata'] ?? '';
                $meta = $raw_meta !== '' ? maybe_unserialize($raw_meta) : [];
                $meta = is_array($meta) ? $meta : [];

                foreach (['_wp_attached_file', '_wp_attachment_metadata'] as $key) {
                    if (($key_counts[$id][$key] ?? 0) > 1) {
                        $this->addFinding(
                            $id,
                            'full',
                            self::DB_DUPLICATE_META,
                            $key,
                            '',
                            'Multiple rows found for this key'
                        );
                    }
                }

                if ($raw_file === '') {
                    $this->addFinding(
                        $id,
                        'full',
                        self::DB_ATTACHED_FILE_MISSING,
                        '',
                        '',
                        '_wp_attached_file missing/empty in DB'
                    );
                }
                if ($raw_meta === '') {
                    $this->addFinding(
                        $id,
                        'full',
                        self::DB_IMAGE_METADATA_MISSING,
                        '',
                        '',
                        '_wp_attachment_metadata missing/empty in DB'
                    );
                } elseif (!$meta) {
                    $this->addFinding(
                        $id,
                        'full',
                        self::DB_IMAGE_METADATA_INVALID,
                        '',
                        '',
                        'Metadata is not a valid nonempty PHP array'
                    );
                }

                $wp_file = get_post_meta($id, '_wp_attached_file', true);
                $wp_meta = get_post_meta($id, '_wp_attachment_metadata', true);
                $db_meta_decoded = $raw_meta !== '' ? maybe_unserialize($raw_meta) : '';
                if ((string) $wp_file !== (string) $raw_file) {
                    $this->addFinding(
                        $id,
                        'full',
                        self::CACHE_ATTACHED_FILE_MISMATCH,
                        $raw_file,
                        '',
                        'WordPress sees: ' . var_export($wp_file, true)
                    );
                }
                if ($wp_meta !== $db_meta_decoded) {
                    $this->addFinding(
                        $id,
                        'full',
                        self::CACHE_IMAGE_METADATA_MISMATCH,
                        'database metadata',
                        '',
                        'WordPress postmeta differs from direct DB result'
                    );
                }

                $file_path = $this->toPath($raw_file, $uploads_dir);
                $meta_file = isset($meta['file']) && is_string($meta['file']) ? $meta['file'] : '';
                $meta_path = $this->toPath($meta_file, $uploads_dir);

                if ($file_path !== '' && is_file($file_path)) {
                    $this->addFinding($id, 'full', self::OK, $raw_file, $file_path);
                } elseif ($raw_file !== '') {
                    $this->addFinding(
                        $id,
                        'full',
                        self::FILE_MISSING,
                        $raw_file,
                        $file_path,
                        'Main attached file is missing from disk'
                    );
                }
                if ($meta && $meta_file === '') {
                    $this->addFinding($id, 'full', self::DB_METADATA_FILE_MISSING, '', '', 'Metadata[file] is missing');
                } elseif ($meta_file !== '') {
                    if (!is_file($meta_path)) {
                        $this->addFinding(
                            $id,
                            'full',
                            self::METADATA_FILE_MISSING,
                            $meta_file,
                            $meta_path,
                            'Metadata points to a nonexisting main image'
                        );
                    }
                    if ($file_path !== '' && $meta_path !== $file_path) {
                        $this->addFinding(
                            $id,
                            'full',
                            self::DB_FILE_PATH_MISMATCH,
                            $meta_file,
                            $meta_path,
                            '_wp_attached_file differs from metadata[file]'
                        );
                    }
                }
                if (is_file($file_path) && $meta) {
                    $dims = $this->dimensionsOf($file_path);
                    if ($dims === false) {
                        $this->addFinding(
                            $id,
                            'full',
                            self::FILE_UNREADABLE_IMAGE,
                            $raw_file,
                            $file_path,
                            'getimagesize() failed; format may be unsupported by PHP'
                        );
                    } elseif (
                        !isset($meta['width'], $meta['height']) ||
                        (int) $meta['width'] !== (int) $dims[0] ||
                        (int) $meta['height'] !== (int) $dims[1]
                    ) {
                        $this->addFinding(
                            $id,
                            'full',
                            self::DB_DIMENSIONS_MISMATCH,
                            ($meta['width'] ?? '?') . 'x' . ($meta['height'] ?? '?'),
                            $file_path,
                            'Actual: ' . $dims[0] . 'x' . $dims[1]
                        );
                    }
                }

                // Big-image scaling: the original upload can be a separate file.
                if (!empty($meta['original_image'])) {
                    $original_path = dirname($meta_path !== '' ? $meta_path : $file_path)
                        . '/' . basename($meta['original_image']);
                    if (!is_file($original_path)) {
                        $this->addFinding(
                            $id,
                            'original',
                            self::ORIGINAL_FILE_MISSING,
                            $meta['original_image'],
                            $original_path
                        );
                    }
                }

                // Check the API's FULL result too (a stale object cache can make it false).
                if (wp_get_attachment_image_src($id, 'full') === false) {
                    $this->addFinding(
                        $id,
                        'full',
                        self::WP_FULL_SRC_FALSE,
                        $raw_file,
                        $file_path,
                        'wp_get_attachment_image_src() returned false'
                    );
                }

                $known_sizes = isset($meta['sizes']) && is_array($meta['sizes']) ? $meta['sizes'] : [];
                if ($all_sizes) {
                    $names = array_unique(array_merge(array_keys($registered), array_keys($known_sizes)));
                } else {
                    $names = $standard_names;
                }

                // Compare missing sizes against original image dimensions when present,
                // consistent with wp_get_missing_image_subsizes().
                $source_dims = $this->dimensionsOf($file_path !== '' ? $file_path : $meta_path);
                if (!empty($meta['original_image']) && !empty($original_path)) {
                    $original_dims = $this->dimensionsOf($original_path);
                    if ($original_dims) {
                        $source_dims = $original_dims;
                    }
                }
                $source_w = $source_dims ? (int) $source_dims[0] : (int) ($meta['width'] ?? 0);
                $source_h = $source_dims ? (int) $source_dims[1] : (int) ($meta['height'] ?? 0);
                $size_dir = dirname(
                    $meta_path !== '' ? $meta_path : ($file_path !== '' ? $file_path : $uploads_dir . '/placeholder')
                );

                foreach ($names as $name) {
                    $saved = $known_sizes[$name] ?? null;
                    $definition = $registered[$name] ?? null;

                    // Disabled/unregistered and absent in DB -> nothing to check.
                    if (!is_array($saved) && !is_array($definition)) {
                        continue;
                    }

                    // No DB record: only report if the currently configured size
                    // COULD be generated from this image without upscaling.
                    if (!is_array($saved)) {
                        if (
                            $source_w > 0 && $source_h > 0 && is_array($definition) &&
                            image_resize_dimensions(
                                $source_w,
                                $source_h,
                                (int) $definition['width'],
                                (int) $definition['height'],
                                $definition['crop']
                            ) !== false
                        ) {
                            $this->addFinding(
                                $id,
                                $name,
                                self::DB_SIZE_MISSING,
                                '',
                                '',
                                'This size is possible with the CURRENT configuration; may have been added after upload'
                            );
                        }
                        continue;
                    }

                    $subfile = isset($saved['file']) && is_string($saved['file']) ? $saved['file'] : '';
                    if ($subfile === '') {
                        $this->addFinding(
                            $id,
                            $name,
                            self::DB_SIZE_FILE_MISSING,
                            '',
                            '',
                            'Size metadata has no filename'
                        );
                        continue;
                    }
                    $subpath = $size_dir . '/' . basename($subfile);
                    if (!is_file($subpath)) {
                        $this->addFinding(
                            $id,
                            $name,
                            self::FILE_MISSING,
                            $subfile,
                            $subpath,
                            'Size exists in DB but file is missing'
                        );
                        continue;
                    }
                    $subdims = $this->dimensionsOf($subpath);
                    if ($subdims === false) {
                        $this->addFinding(
                            $id,
                            $name,
                            self::FILE_UNREADABLE_IMAGE,
                            $subfile,
                            $subpath,
                            'getimagesize() failed; format may be unsupported by PHP'
                        );
                        continue;
                    }
                    if (
                        !isset($saved['width'], $saved['height']) ||
                        (int) $saved['width'] !== (int) $subdims[0] ||
                        (int) $saved['height'] !== (int) $subdims[1]
                    ) {
                        $this->addFinding(
                            $id,
                            $name,
                            self::DB_DIMENSIONS_MISMATCH,
                            ($saved['width'] ?? '?') . 'x' . ($saved['height'] ?? '?'),
                            $subpath,
                            'Actual: ' . $subdims[0] . 'x' . $subdims[1]
                        );
                        continue;
                    }
                    $this->addFinding(
                        $id,
                        $name,
                        self::OK,
                        $subfile,
                        $subpath,
                        $subdims[0] . 'x' . $subdims[1]
                    );
                }
            }
            unset($rows, $db, $key_counts, $ids);
        }

        if ($single_id > 0 && $checked_attachments === 0) {
            WP_CLI::error(sprintf('Image attachment %d was not found.', $single_id));
        }

        $this->renderResults($assoc_args['format'] ?? 'table', $checked_attachments);

        if ($this->issueCount > 0) {
            WP_CLI::halt(1);
        }
    }

    private function toPath(string $filename, string $uploadsDir): string
    {
        if ($filename === '') {
            return '';
        }

        // Preserve absolute Unix and Windows drive paths.
        if ($filename[0] === '/' || preg_match('~^[A-Za-z]:[\\\\/]~', $filename)) {
            return $filename;
        }

        return $uploadsDir . '/' . ltrim(str_replace('\\', '/', $filename), '/');
    }

    /** @return array<int|string, mixed>|false */
    private function dimensionsOf(string $path): array|false
    {
        if ($path === '' || !is_file($path)) {
            return false;
        }

        return @getimagesize($path);
    }

    private function addFinding(
        int $id,
        string $size,
        string $status,
        string $dbValue,
        string $path,
        string $detail = ''
    ): void {
        if ($status !== self::OK) {
            $this->issueCount++;
        } elseif (!$this->includeOk) {
            return;
        }

        $this->findings[] = [
            'attachment_id' => $id,
            'size' => $size,
            'status' => $status,
            'db_value' => $dbValue,
            'file_path' => $path,
            'detail' => $detail,
        ];
    }

    private function renderResults(string $format, int $checkedAttachments): void
    {
        if ($format !== 'table' || $this->findings !== []) {
            format_items(
                $format,
                $this->findings,
                ['attachment_id', 'size', 'status', 'db_value', 'file_path', 'detail']
            );
        }

        // Keep machine-readable output free of human-readable summaries.
        if ($format !== 'table') {
            return;
        }

        if ($this->issueCount > 0) {
            WP_CLI::warning(sprintf(
                'Found %d %s across %d checked image %s.',
                $this->issueCount,
                $this->issueCount === 1 ? 'issue' : 'issues',
                $checkedAttachments,
                $checkedAttachments === 1 ? 'attachment' : 'attachments'
            ));
            return;
        }

        WP_CLI::success(sprintf(
            'Checked %d image %s. No issues found.',
            $checkedAttachments,
            $checkedAttachments === 1 ? 'attachment' : 'attachments'
        ));
    }
}

WP_CLI::add_command('media audit-sizes', AttachmentSizeAudit::class);
