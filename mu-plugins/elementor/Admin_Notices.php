<?php

namespace Elementor\Core\Admin;

use Elementor\Core\Base\Module;

class Admin_Notices extends Module
{
    public function __construct()
    {
    }

    public function get_name()
    {
        return 'admin-notices';
    }

    public function print_admin_notice(array $options, $exclude_pages = [])
    {
    }

    public static function add_plg_campaign_data($url, $campaign_data)
    {
        return $url;
    }
}
