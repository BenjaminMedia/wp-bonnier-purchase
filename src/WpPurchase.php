<?php

namespace Bonnier\WP\Purchase;

use Bonnier\WP\Purchase\Assets\Scripts;
use Bonnier\WP\Purchase\Http\Ajax;
use Bonnier\WP\Purchase\Http\Routes;
use Bonnier\WP\Purchase\Interfaces\UserInterface;
use Bonnier\WP\Purchase\Providers\CommonLoginUserProvider;
use Bonnier\WP\Purchase\Repositories\PurchaseManagerRepository;
use Bonnier\WP\Purchase\Settings\SettingsPage;

class WpPurchase
{
    /** Text domain for translators */
    const TEXT_DOMAIN = 'bonnier-purchase';

    /** @var WpPurchase Instance of this class */
    private static $instance;

    /** @var string Directory of this class */
    private $dir;

    /** @var string Basename of this class */
    private $basename;

    /** @var string Plugins directory for this plugin */
    private $plugin_dir;

    /** @var string Plugins url for this plugin */
    private $plugin_url;

    /** @var SettingsPage */
    private $settings;

    /** @var UserInterface */
    private $userProvider;

    /** @var PurchaseManagerRepository */
    private $serviceRepo;

    /** @var Routes */
    private $routes;

    public function __construct()
    {
        // Set plugin file variables
        $this->dir = __DIR__;
        $this->basename = plugin_basename($this->dir);
        $this->plugin_dir = plugin_dir_path($this->dir);
        $this->plugin_url = plugin_dir_url($this->dir);

        // Load textdomain
        load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname($this->basename) . '/languages');
    }

    public static function instance()
    {
        if(!self::$instance) {
            self::$instance = new self;
            global $bonnier_purchase;
            $bonnier_purchase = self::$instance;

            self::$instance->bootstrap();

            do_action('bonnier_purchase_loaded');
        }

        return self::$instance;
    }

    public function bootstrap()
    {
        $this->settings = new SettingsPage();
        $this->userProvider = new CommonLoginUserProvider();
        $this->serviceRepo = new PurchaseManagerRepository();
        $this->routes = new Routes();
    }

    /**
     * @return string
     */
    public function getPluginUrl()
    {
        return $this->plugin_url;
    }

    /**
     * @return SettingsPage
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return UserInterface
     */
    public function getUserProvider()
    {
        return $this->userProvider;
    }

    /**
     * @return PurchaseManagerRepository
     */
    public function getServiceRepository()
    {
        return $this->serviceRepo;
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}
