<?php

/*
 * Plugin Name: YITH premium plugins
 * Plugin URI: https://yithemes.com/product-category/plugins/
 */

// Disable updates
function YIT_Upgrade()
{
    return new class {
        public function register($slug, $init) {
        }
    };
}
