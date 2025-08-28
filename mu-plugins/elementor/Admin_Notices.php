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

    public static function add_plg_campaign_data($url, $campaign_data)
    {
        return $url;
    }
}
