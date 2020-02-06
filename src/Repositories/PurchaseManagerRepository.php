<?php

namespace Bonnier\WP\Purchase\Repositories;

use Bonnier\WP\OAuth\Http\Routes;
use Bonnier\WP\OAuth\WpOAuth;
use Bonnier\WP\Purchase\Services\PurchaseManagerService;
use Bonnier\WP\Purchase\WpPurchase;

class PurchaseManagerRepository
{
    /** @var PurchaseManagerService */
    private $service;
    private $homeUrl;

    public function __construct()
    {
        $this->service = new PurchaseManagerService(WpPurchase::instance()->getSettings()->getPurchaseURL());
        if(function_exists('pll_home_url')) {
            $this->homeUrl = pll_home_url();
        } else {
            $this->homeUrl = home_url('/');
        }
    }

    public function checkAccess($product_id)
    {
        return $this->service->hasAccess(
            $product_id,
            WpPurchase::instance()->getUserProvider()->getIdentifier()
        );
    }

    public function getPaymentUrl($productId, $callbackUrl = false, $paymentPreviewAttributes = [], $userIdentifier = false)
    {
        if(!$callbackUrl){
            $callbackUrl = $this->homeUrl;
        }
        if(!$userIdentifier){
            $userIdentifier = WpPurchase::instance()->getUserProvider()->getIdentifier();
            if(!WpOAuth::instance()->getUserRepo()->isAuthenticated() || !$userIdentifier){
                $purchaseUri = sprintf(
                    '%s?product_id=%s&callback=%s&payment_attributes=%s',
                    urlencode(WpPurchase::instance()->getRoutes()->getPurchaseUri()),
                    urlencode($productId),
                    urlencode($callbackUrl),
                    urlencode(json_encode($paymentPreviewAttributes))
                );

                $loginUri = sprintf(
                    '%s?redirect_uri=%s',
                    WpOAuth::instance()->getRoutes()->getURI(Routes::LOGIN_ROUTE),
                    urlencode($purchaseUri)
                );
                return $loginUri;
            }
        }

        $settings = WpPurchase::instance()->getSettings();

        $url = sprintf(
            '%s/has_access?access_token=%s&product_id=%s&callback=%s&site_id=%s%s',
            trim($settings->getPurchaseURL(), '/'),
            urlencode($userIdentifier),
            urlencode($productId),
            urlencode($callbackUrl),
            urlencode($settings->getSiteId()),
            $this->paymentPreviewParameters($paymentPreviewAttributes)
        );

        return $url;
    }
    
    public function getLoginPaymentUrl($productId, $callbackUrl = false, $paymentPreviewAttributes)
    {
        $purchaseUri = sprintf(
            '%s?product_id=%s&callback=%s&payment_attributes=%s',
            urlencode(WpPurchase::instance()->getRoutes()->getPurchaseUri()),
            urlencode($productId),
            urlencode($callbackUrl),
            urlencode(json_encode($paymentPreviewAttributes))
        );
    
        $loginUri = sprintf(
            '%s?redirect_uri=%s',
            WpOAuth::instance()->getRoutes()->getURI(Routes::REGISTER_ROUTE),
            urlencode($purchaseUri)
        );
        return $loginUri;
    }

    public function paymentPreviewParameters($paymentArticlePreviewAttributes)
    {
        $attributes = '';
        foreach($paymentArticlePreviewAttributes as $key => $attribute){
            $attributes .= '&'.$key.'='. urlencode($attribute);
        }
        if(!empty($attributes)){
            return $attributes;
        }
        return null;
    }

    public function getHistory()
    {
        return $this->service->getHistory(
            WpPurchase::instance()->getUserProvider()->getIdentifier()
        );
    }

    public function generateSubscriptionUrl($title, $imageUrl, $callbackUrl)
    {
        $subscriptionUrl = WpPurchase::instance()->getSettings()->getSubscriptionURL();
        $parts = parse_url($subscriptionUrl);

        $query = sprintf(
            'title=%s&image=%s&returnUrl=%s&oauthClientId=%s',
            urlencode($title),
            urlencode($imageUrl),
            urlencode(WpPurchase::instance()->getRoutes()->getCallbackUri() . '?redirectUri=' . $callbackUrl),
            urlencode(WpOauth::instance()->getSettings()->get_api_user())
        );

        if (empty($parts['query'])) {
            return $subscriptionUrl.'?'.$query;
        } else {
            return $subscriptionUrl.'&'.$query;
        }
    }
}
