<?php

/**
 * Plugin Name: Bonnier Purchase Plugin
 * Version: 1.0.4
 * Plugin URI: https://github.com/BenjaminMedia/wp-bonnier-purchase
 * Description: This plugin allows you to integrate your site with a purchase service
 * Author: Bonnier
 * License: GPL v3
 */

if(!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/src/api.php';

spl_autoload_register(function ($className) {
    $namespace = 'Bonnier\\WP\\Purchase\\';
    if (str_contains($className, $namespace)) {
        $className = str_replace([$namespace, '\\'], [__DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $className);

        $file = $className . '.php';

        if(file_exists($file)) {
            require_once($className . '.php');
        } else {
            throw new Exception(sprintf('\'%s\' does not exist!', $file));
        }
    }
});

function register_bonnier_purchase()
{
    return \Bonnier\WP\Purchase\WpPurchase::instance();
}

add_action('plugins_loaded', 'register_bonnier_purchase');
