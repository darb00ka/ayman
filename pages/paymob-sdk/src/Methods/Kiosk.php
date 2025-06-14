<?php

namespace Alaa\Paymob\Methods;

use Alaa\Paymob\Client;
use Alaa\Paymob\EntryPayment\PaymentKeyRequest;
use Alaa\Paymob\Exceptions\PaymentException;
use Alaa\Paymob\URLS;
use GuzzleHttp\Exception\GuzzleException;

class Kiosk
{



    /**
     * @throws PaymentException
     */
    public static function pay(array $params, PaymentKeyRequest $paymentKeyRequest): array
    {
        try {


            return Client::requestJson("post", URLS::PAY, [
                "json" => [
                    "source" => [
                        "identifier" => "AGGREGATOR",
                        "subtype" => "AGGREGATOR"
                    ],
                    "payment_token" => $paymentKeyRequest->getPaymentToken($params)["token"]
                ]
            ]);
        }catch (GuzzleException $e){
            throw  new PaymentException($e->getMessage() , $e->getCode());
        }
    }
}