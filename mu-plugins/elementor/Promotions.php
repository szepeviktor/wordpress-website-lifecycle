<?php

namespace Elementor\Modules\Promotions;

use Elementor\Core\Base\Module as Base_Module;

class Module extends Base_Module
{
    const ADMIN_MENU_PRIORITY = 100;

    public function __construct()
    {
    }

    public function get_name()
    {
        return 'promotions';
    }
}
