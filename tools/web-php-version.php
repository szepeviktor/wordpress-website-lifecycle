<?php

declare(strict_types=1);

/*
 * Show the PHP version used by WordPress HTTP requests.
 *
 * This file is loadable with WP-CLI's --require flag:
 *
 * wp --require=web-php-version.php core web-php-version
 */

namespace SzepeViktor\WordPress\Cli;

use WP_CLI;

final class WebPhpVersion
{
    /**
     * Shows the PHP version used by the web server.
     *
     * The command creates a temporary MU plugin that exposes PHP_VERSION on a
     * random REST API route, requests that route over HTTP, then removes the
     * temporary file. This reports the frontend PHP runtime instead of the
     * WP-CLI PHP runtime.
     *
     * ## EXAMPLES
     *
     *     $ wp core web-php-version
     *     8.3.12
     *
     * @when after_wp_load
     *
     * @param array<int, string> $args
     * @param array<string, mixed> $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        $mu_plugin_dir = wp_normalize_path(WPMU_PLUGIN_DIR);

        if (!is_dir($mu_plugin_dir) && !wp_mkdir_p($mu_plugin_dir)) {
            WP_CLI::error(sprintf('Unable to create MU plugin directory: %s', $mu_plugin_dir));
        }

        if (!is_writable($mu_plugin_dir)) {
            WP_CLI::error(sprintf('MU plugin directory is not writable: %s', $mu_plugin_dir));
        }

        $route = sprintf('phpver-%s', wp_generate_password(12, false, false));
        $plugin_file = sprintf('%s/%s.php', $mu_plugin_dir, $route);

        try {
            $this->writeTemporaryPlugin($plugin_file, $route);

            $endpoint = sprintf(
                '%s/wp-json/%s/v1',
                untrailingslashit(home_url()),
                rawurlencode($route)
            );
            $response = wp_remote_get(
                $endpoint,
                [
                    'headers' => ['Accept' => 'text/plain'],
                    'redirection' => 0,
                    'timeout' => 15,
                ]
            );

            if (is_wp_error($response)) {
                WP_CLI::error(sprintf(
                    'Unable to request temporary REST endpoint: %s',
                    $response->get_error_message()
                ));
            }

            $response_code = wp_remote_retrieve_response_code($response);

            if ($response_code !== 200) {
                WP_CLI::error(sprintf(
                    'Temporary REST endpoint returned HTTP status %d: %s',
                    $response_code,
                    $endpoint
                ));
            }

            $body = trim(wp_remote_retrieve_body($response));
            $version = json_decode($body);

            if (!is_string($version)) {
                WP_CLI::error('Temporary REST endpoint returned an invalid response.');
            }

            WP_CLI::line($version);
        } finally {
            if (is_file($plugin_file) && !unlink($plugin_file)) {
                WP_CLI::warning(sprintf('Unable to remove temporary MU plugin: %s', $plugin_file));
            }
        }
    }

    private function writeTemporaryPlugin(string $plugin_file, string $route): void
    {
        $contents = <<<'PHP'
<?php

add_action('rest_api_init', function () {
    register_rest_route(
        '%s',
        '/v1',
        array(
            'methods' => 'GET',
            'callback' => function () {
                return PHP_VERSION;
            },
            'permission_callback' => '__return_true',
        )
    );
});
PHP;

        $written = file_put_contents($plugin_file, sprintf($contents, $route), LOCK_EX);

        if (false === $written) {
            WP_CLI::error(sprintf('Unable to write temporary MU plugin: %s', $plugin_file));
        }
    }
}

WP_CLI::add_command('core web-php-version', WebPhpVersion::class);
