<?php

namespace Bonnier\WP\Purchase\Http;

use Bonnier\WP\Purchase\Helpers\RedirectHelper;

class Routes
{
    const BASE_PREFIX = 'wp-json';

    const PLUGIN_PREFIX = 'bonnier-purchase';

    const CALLBACK_ROUTE = '/callback';

    private $homeUrl;

    public function __construct()
    {
        if(function_exists('pll_home_url')) {
            $this->homeUrl = pll_home_url();
        } else {
            $this->homeUrl = home_url('/');
        }

        add_action('rest_api_init', function () {
            register_rest_route(self::PLUGIN_PREFIX, self::CALLBACK_ROUTE, [
                'methods' => 'GET, POST',
                'callback' => [$this, 'callback'],
            ]);
        });
    }

    public function callback(\WP_REST_Request $request)
    {
        RedirectHelper::redirect($request->get_param('redirectUri'));
    }

    public function getUri()
    {
        return sprintf(
            '%s/%s/%s/%s',
            trim($this->homeUrl, '/'),
            static::BASE_PREFIX,
            static::PLUGIN_PREFIX,
            trim(static::CALLBACK_ROUTE, '/')
            );
    }
}
