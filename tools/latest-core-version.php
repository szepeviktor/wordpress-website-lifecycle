<?php

declare(strict_types=1);

/*
 * Show the latest WordPress core version, optionally limited to a branch.
 *
 * This file is loadable with WP-CLI's --require flag:
 *
 * wp --require=latest-core-version.php core latest-version
 * wp --require=latest-core-version.php core latest-version --branch=4.9
 */

namespace SzepeViktor\WordPress\Cli;

use JsonException;
use WP_CLI;

use function WP_CLI\Utils\http_request;

final class LatestCoreVersion
{
    private const VERSION_CHECK_API = 'https://api.wordpress.org/core/version-check/1.7/';

    /**
     * Shows the latest WordPress core version.
     *
     * ## OPTIONS
     *
     * [--branch=<branch>]
     * : Limit the result to a major.minor branch, for example 4.9.
     *
     * ## EXAMPLES
     *
     *     # Show the latest version in the latest branch.
     *     $ wp core latest-version
     *
     *     # Show the latest version in the 4.9 branch.
     *     $ wp core latest-version --branch=4.9
     *
     * @when before_wp_load
     *
     * @param array<int, string> $args
     * @param array<string, mixed> $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        $branch = $assoc_args['branch'] ?? null;

        if (null !== $branch && (!is_string($branch) || 1 !== preg_match('/^\d+\.\d+$/D', $branch))) {
            WP_CLI::error('Branch must have major.minor form, for example 4.9.');
        }

        $response = http_request('GET', self::VERSION_CHECK_API);

        try {
            $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            WP_CLI::error(sprintf('WordPress.org returned invalid JSON: %s', $exception->getMessage()));
        }

        if (!is_array($data) || !isset($data['offers']) || !is_array($data['offers'])) {
            WP_CLI::error('WordPress.org returned an invalid version response.');
        }

        $versions = [];

        foreach ($data['offers'] as $offer) {
            if (!is_array($offer) || !isset($offer['version']) || !is_string($offer['version'])) {
                continue;
            }

            $version = $offer['version'];

            if (1 !== preg_match('/^\d+\.\d+(?:\.\d+)?$/D', $version)) {
                continue;
            }

            if (null !== $branch && $version !== $branch && !str_starts_with($version, $branch.'.')) {
                continue;
            }

            $versions[$version] = true;
        }

        if ([] === $versions) {
            if (null === $branch) {
                WP_CLI::error('WordPress.org did not advertise a stable core release.');
            }

            WP_CLI::error(sprintf(
                'WordPress.org did not advertise a stable release for branch %s.',
                $branch
            ));
        }

        $versions = array_keys($versions);
        usort($versions, 'version_compare');

        WP_CLI::line((string) end($versions));
    }
}

WP_CLI::add_command('core latest-version', LatestCoreVersion::class);
