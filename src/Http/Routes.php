<?php

namespace Bonnier\WP\Purchase\Http;

use Bonnier\WP\OAuth\WpOAuth;
use Bonnier\WP\Purchase\Helpers\RedirectHelper;
use Bonnier\WP\Purchase\WpPurchase;

class Routes
{
    const BASE_PREFIX = 'wp-json';

    const PLUGIN_PREFIX = 'bonnier-purchase';

    const CALLBACK_ROUTE = '/callback';

    const PURCHASE_ROUTE = '/purchase';

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
            register_rest_route(self::PLUGIN_PREFIX, self::PURCHASE_ROUTE, [
                'methods' => 'GET, POST',
                'callback' => [$this, 'purchase'],
            ]);
        });
    }

    public function callback(\WP_REST_Request $request)
    {
        RedirectHelper::redirect($request->get_param('redirectUri'));
    }

    public function purchase(\WP_REST_Request $request)
    {
        $productId = $request->get_param('product_id');
        $callback = $request->get_param('callback');
        $paymentAttributes = $request->get_param('payment_attributes');

        $userIdentifier = WpPurchase::instance()->getUserProvider()->getIdentifier();

        if(!$userIdentifier) {
            $purchaseUri = sprintf(
                '%s?product_id=%s&callback=%s&payment_attributes=%s',
                urlencode($this->getPurchaseUri()),
                urlencode($productId),
                urlencode($callback),
                urlencode($paymentAttributes)
            );

            $loginUri = sprintf(
                '%s?redirect_uri=%s',
                WpOAuth::instance()->getRoutes()->getURI(\Bonnier\WP\OAuth\Http\Routes::LOGIN_ROUTE),
                urlencode($purchaseUri)
            );
            RedirectHelper::redirect($loginUri);
        }

        $paymentAttributes = json_decode($paymentAttributes);

        RedirectHelper::redirect(WpPurchase::instance()->getServiceRepository()->getPaymentUrl($productId, $callback, $paymentAttributes, $userIdentifier));
    }

    public function getCallbackUri()
    {
        return sprintf(
            '%s/%s/%s/%s',
            trim($this->homeUrl, '/'),
            static::BASE_PREFIX,
            static::PLUGIN_PREFIX,
            trim(static::CALLBACK_ROUTE, '/')
        );
    }

    public function getPurchaseUri()
    {
        return sprintf(
            '%s/%s/%s/%s',
            trim($this->homeUrl, '/'),
            static::BASE_PREFIX,
            static::PLUGIN_PREFIX,
            trim(static::PURCHASE_ROUTE, '/')
        );
    }
}
