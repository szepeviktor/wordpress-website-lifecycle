<?php

declare(strict_types=1);

/*
 * Find non-silent index.php files in the WordPress content directory.
 *
 * This file is loadable with WP-CLI's --require flag:
 *
 * wp --require=non-silent-indices.php core non-silent-indices
 */

namespace SzepeViktor\WordPress\Cli;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;
use WP_CLI;

use function WP_CLI\Utils\format_items;

final class NonSilentIndices
{
    /**
     * Finds non-silent index.php files under WP_CONTENT_DIR.
     *
     * An index is silent when it contains only one PHP opening tag,
     * whitespace, and comments.
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
     *     $ wp core non-silent-indices
     *     plugins/example/index.php
     *
     *     $ wp core non-silent-indices --format=count
     *     1
     *
     * @when after_wp_load
     *
     * @param array<int, string> $args
     * @param array<string, mixed> $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        $content_dir = wp_normalize_path(WP_CONTENT_DIR);

        if (!is_dir($content_dir)) {
            WP_CLI::error(sprintf('WordPress content directory does not exist: %s', $content_dir));
        }

        if (!is_readable($content_dir)) {
            WP_CLI::error(sprintf('WordPress content directory is not readable: %s', $content_dir));
        }

        $findings = [];

        try {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $content_dir,
                    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
                )
            );

            foreach ($files as $file) {
                if (
                    !$file->isFile()
                    || $file->isLink()
                    || 'index.php' !== $file->getFilename()
                    || $this->isSilent($file->getPathname())
                ) {
                    continue;
                }

                $path = wp_normalize_path($file->getPathname());
                $findings[] = [
                    'file' => ltrim(substr($path, strlen($content_dir)), '/'),
                ];
            }
        } catch (Throwable $throwable) {
            WP_CLI::error(sprintf('Unable to inspect WordPress content: %s', $throwable->getMessage()));
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
            return;
        }

        format_items($format, $findings, ['file']);
    }

    private function isSilent(string $path): bool
    {
        $contents = file_get_contents($path);

        if (false === $contents) {
            throw new RuntimeException(sprintf('Unable to read file: %s', $path));
        }

        $has_open_tag = false;

        foreach (token_get_all($contents) as $token) {
            if (!is_array($token)) {
                return false;
            }

            [$token_id, $token_text] = $token;

            if (T_OPEN_TAG === $token_id) {
                if ($has_open_tag || '<?php' !== trim($token_text)) {
                    return false;
                }

                $has_open_tag = true;
                continue;
            }

            if (in_array($token_id, [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            if (T_INLINE_HTML === $token_id && '' === trim($token_text)) {
                continue;
            }

            return false;
        }

        return $has_open_tag;
    }
}

WP_CLI::add_command('core non-silent-indices', NonSilentIndices::class);
