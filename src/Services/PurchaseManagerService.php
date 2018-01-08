<?php

namespace Bonnier\WP\Purchase\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class PurchaseManagerService
{
    /** @var Client */
    private $client;

    public function __construct($base_uri)
    {
        $this->client = new Client([
            'base_uri' => $base_uri,
        ]);
    }

    public function hasAccess($product_id, $access_token)
    {
        if(!$product_id || !$access_token) {
            return false;
        }

        try {
            $response = $this->client->post('has_access', [
                    'form_params' => [
                        'product_id' => $product_id,
                        'access_token' => $access_token,
                    ],
                    'header' => [
                        'accept' => 'application/json'
                    ]]
            );

            $result = json_decode($response->getBody()->getContents());
            return $result && property_exists($result, 'has_access') && $result->has_access;
        } catch(ClientException $e) {
            return false;
        }
    }

    public function getHistory($access_token)
    {
        if(!$access_token) {
            return null;
        }
        try {
            $result = $this->client->get('history', [
                'query' => [
                    'dir' => 'DESC',
                    'access_token' => urlencode($access_token),
                ],
            ]);
        } catch(ClientException $e) {
            return null;
        }

        $ids = json_decode($result->getBody()->getContents());
        if (json_last_error() || empty($ids)) {
            return null;
        }

        return $ids;
    }
}
