<?php

declare(strict_types=1);

/*
 * Find plugin and theme translation files with unknown locale codes.
 *
 * This file is loadable with WP-CLI's --require flag:
 *
 * wp --require=invalid-language-files.php core invalid-language-files
 */

namespace SzepeViktor\WordPress\Cli;

use FilesystemIterator;
use JsonException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use WP_CLI;

use function WP_CLI\Utils\format_items;

final class InvalidLanguageFiles
{
    private const CORE_TRANSLATIONS_API = 'https://api.wordpress.org/translations/core/1.0/';

    /**
     * Finds plugin and theme .mo files whose locale is not advertised by WordPress.org.
     *
     * The locale is the part after the final hyphen in the filename, or the
     * entire filename when it contains no hyphen.
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
     *     $ wp core invalid-language-files
     *     plugins/example/languages/example-invalid.mo
     *
     *     $ wp core invalid-language-files --format=table
     *     +------------------------------------------------------+---------+
     *     | file                                                 | locale  |
     *     +------------------------------------------------------+---------+
     *     | themes/example/languages/example-invalid.mo          | invalid |
     *     +------------------------------------------------------+---------+
     *
     * @when after_wp_load
     *
     * @param array<int, string> $args
     * @param array<string, mixed> $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        $directories = [
            'plugins' => wp_normalize_path(WP_PLUGIN_DIR),
            'themes' => wp_normalize_path(get_theme_root()),
        ];

        foreach ($directories as $type => $directory) {
            if (!is_dir($directory)) {
                WP_CLI::error(sprintf('WordPress %s directory does not exist: %s', $type, $directory));
            }

            if (!is_readable($directory)) {
                WP_CLI::error(sprintf('WordPress %s directory is not readable: %s', $type, $directory));
            }
        }

        $locales = $this->getAvailableLocales();
        $findings = [];

        try {
            foreach ($directories as $type => $directory) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        $directory,
                        FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
                    )
                );

                foreach ($files as $file) {
                    if (!$file->isFile() || $file->isLink() || 'mo' !== $file->getExtension()) {
                        continue;
                    }

                    $filename = $file->getBasename('.mo');
                    $separator_position = strrpos($filename, '-');
                    $locale = false === $separator_position
                        ? $filename
                        : substr($filename, $separator_position + 1);

                    if (isset($locales[$locale])) {
                        continue;
                    }

                    $path = wp_normalize_path($file->getPathname());
                    $findings[] = [
                        'file' => sprintf(
                            '%s/%s',
                            $type,
                            ltrim(substr($path, strlen($directory)), '/')
                        ),
                        'locale' => $locale,
                    ];
                }
            }
        } catch (Throwable $throwable) {
            WP_CLI::error(sprintf(
                'Unable to inspect plugin and theme translation files: %s',
                $throwable->getMessage()
            ));
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
            format_items($format, $findings, ['file', 'locale']);
        }

        if ([] !== $findings) {
            WP_CLI::halt(1);
        }
    }

    /**
     * @return array<string, true>
     */
    private function getAvailableLocales(): array
    {
        $response = wp_remote_get(self::CORE_TRANSLATIONS_API);

        if (is_wp_error($response)) {
            WP_CLI::error(sprintf(
                'Unable to retrieve WordPress core translations: %s',
                $response->get_error_message()
            ));
        }

        $response_code = wp_remote_retrieve_response_code($response);

        if (200 !== $response_code) {
            WP_CLI::error(sprintf(
                'WordPress.org returned HTTP status %d for core translations.',
                $response_code
            ));
        }

        try {
            $data = json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            WP_CLI::error(sprintf('WordPress.org returned invalid JSON: %s', $exception->getMessage()));
        }

        if (!is_array($data) || !isset($data['translations']) || !is_array($data['translations'])) {
            WP_CLI::error('WordPress.org returned an invalid core translations response.');
        }

        $locales = ['en_US' => true];

        foreach ($data['translations'] as $translation) {
            if (
                !is_array($translation)
                || !isset($translation['language'])
                || !is_string($translation['language'])
                || '' === $translation['language']
            ) {
                WP_CLI::error('WordPress.org returned a malformed core translation.');
            }

            $locales[$translation['language']] = true;
        }

        return $locales;
    }
}

WP_CLI::add_command('core invalid-language-files', InvalidLanguageFiles::class);
