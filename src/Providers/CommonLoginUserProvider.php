<?php

namespace Bonnier\WP\Purchase\Providers;

use Bonnier\WP\OAuth\Services\AccessTokenService;
use Bonnier\WP\Purchase\Interfaces\UserInterface;

class CommonLoginUserProvider implements UserInterface
{
    public function getIdentifier()
    {
        $tokenFromStorage = AccessTokenService::getFromStorage();
        if($tokenFromStorage) {
            return $tokenFromStorage->getToken();
        }

        return null;
    }
}
