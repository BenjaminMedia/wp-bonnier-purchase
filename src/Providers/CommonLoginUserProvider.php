<?php

namespace Bonnier\WP\Purchase\Providers;

use Bonnier\WP\OAuth\Services\AccessTokenService;
use Bonnier\WP\OAuth\WpOAuth;
use Bonnier\WP\Purchase\Interfaces\UserInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class CommonLoginUserProvider implements UserInterface
{
    /**
     * @return null|string
     */
    public function getIdentifier()
    {
        $tokenFromStorage = AccessTokenService::getFromStorage();
        if($tokenFromStorage) {
            return $tokenFromStorage->getToken();
        }

        return null;
    }
    
    /**
     * @return bool
     */
    public function validateIdentifier()
    {
        $accessToken = AccessTokenService::getFromStorage();
        if($accessToken) {
            /** @var ResourceOwnerInterface $user */
            $user = WpOAuth::instance()->getUserRepo()->getUserByAccessToken($accessToken);
            return !is_null($user->getId());
        }
        
        return false;
    }
}
