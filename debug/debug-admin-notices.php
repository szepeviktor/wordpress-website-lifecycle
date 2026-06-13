<?php

/*
 * Plugin Name: Log admin notices (DBG)
 * Description: Logs admin notices for debugging, great for finding admin_notices to hide.
 */

declare(strict_types=1);

namespace SzepeViktor\WordPress;

use WP_User;

final class DebugAdminNoticesLogger
{
    private const LOG_FILE = WP_CONTENT_DIR . '/debug-admin-notices.log';

    /**
     * @var array<string, true>
     */
    private array $activeHooks = [];

    public static function boot(): void
    {
        (new self())->registerHooks();
    }

    private function registerHooks(): void
    {
        foreach (['admin_notices', 'network_admin_notices', 'user_admin_notices', 'all_admin_notices'] as $hookName) {
            add_action($hookName, [$this, 'start'], PHP_INT_MIN);
            add_action($hookName, [$this, 'end'], PHP_INT_MAX);
        }
    }

    public function start(): void
    {
        $hookName = current_filter();
        if (isset($this->activeHooks[$hookName])) {
            return;
        }

        $this->activeHooks[$hookName] = true;
        ob_start();
    }

    public function end(): void
    {
        $hookName = current_filter();
        if (!isset($this->activeHooks[$hookName])) {
            return;
        }

        unset($this->activeHooks[$hookName]);

        $output = (string) ob_get_clean();
        if ($output === '') {
            return;
        }

        $user = wp_get_current_user();
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $logItem = implode(' ', [
            '[' . gmdate('c') . ']',
            'user=' . ($user instanceof WP_User ? $user->ID : 0),
            'screen=' . (($screen && !empty($screen->id)) ? $screen->id : 'unknown'),
            'hook=' . $hookName,
            'notice=' . wp_json_encode(wp_strip_all_tags($output), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        error_log($logItem . "\n", 3, self::LOG_FILE);

        echo $output;
    }
}

DebugAdminNoticesLogger::boot();
