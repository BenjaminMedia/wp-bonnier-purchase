<?php

namespace Bonnier\WP\Purchase\Repositories;

use Bonnier\WP\OAuth\Services\AccessTokenService;
use Bonnier\WP\OAuth\WpOAuth;
use Bonnier\WP\Purchase\Services\PurchaseManagerService;
use Bonnier\WP\Purchase\WpPurchase;
use League\OAuth2\Client\Token\AccessToken;

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
            AccessTokenService::getFromStorage()->getToken()
        );
    }

    public function getPaymentUrl($productId, $callbackUrl = false, $paymentPreviewAttributes = [], $accessToken = false)
    {
        if(!$callbackUrl){
            $callbackUrl = $this->homeUrl;
        }

        if(!$accessToken){
            $accessToken = AccessTokenService::getFromStorage() ? AccessTokenService::getFromStorage()->getToken() : null;
            if(!WpOAuth::instance()->getUserRepo()->isAuthenticated() || !$accessToken){
                return $callbackUrl;
            }
        }

        return WpPurchase::instance()->getSettings()->getPurchaseURL().
            'has_access?access_token='.urlencode($accessToken).
            '&product_id='.urlencode($productId).
            '&callback='.urlencode($callbackUrl).
            '&site_id='.WpPurchase::instance()->getSettings()->getSiteId().
            $this->paymentPreviewParameters($paymentPreviewAttributes);
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
            AccessTokenService::getFromStorage()->getToken()
        );
    }
}
