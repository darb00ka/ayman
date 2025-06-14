<?php

namespace Alaa\Paymob;

use GuzzleHttp\Exception\GuzzleException;

class Client
{


    public static ?\GuzzleHttp\Client $client = null;

    /**
     *
     * @return \GuzzleHttp\Client
     */
    public static function getClient(): \GuzzleHttp\Client
    {
        if (self::$client === null) {
            self::$client = new \GuzzleHttp\Client(['base_uri' => URLS::Base]);
        }
        return self::$client;
    }


    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return mixed
     * @throws GuzzleException
     */
    public static function requestJson(string $method , string $uri , array $options = array())
    {

        $req = self::getClient()->request($method,$uri , $options);
        return json_decode($req->getBody()->getContents() , true);
    }
}