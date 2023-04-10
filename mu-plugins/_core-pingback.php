<?php

/*
 * Plugin Name: Disable pingback revealing real IP address behind a firewall or proxy
 * Plugin URI: https://www.netsparker.com/blog/web-security/xml-rpc-protocol-ip-disclosure-attacks/
 */

add_filter(
    'xmlrpc_methods',
    static function ($methods) {
        unset($methods['pingback.ping']);
        return $methods;
    },
    11,
    1
);
