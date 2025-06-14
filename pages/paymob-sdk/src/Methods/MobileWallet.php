<?php

namespace Alaa\Paymob\Methods;

use Alaa\Paymob\Client;
use Alaa\Paymob\EntryPayment\PaymentKeyRequest;
use Alaa\Paymob\Exceptions\PaymentException;
use Alaa\Paymob\URLS;
use GuzzleHttp\Exception\GuzzleException;

class MobileWallet
{

    /**
     * @throws PaymentException
     */
    public static function pay(array $params, PaymentKeyRequest $paymentKeyRequest, string $number): array
    {
        try {
            return Client::requestJson("post", URLS::PAY, [
                "json" => [
                    "source" => [
                        "identifier" => $number,
                        "subtype" => "WALLET"
                    ],
                    "payment_token" => $paymentKeyRequest->getPaymentToken($params)["token"]
                ]
            ]);
        }catch (GuzzleException $e){
            throw  new PaymentException($e->getMessage() , $e->getCode());
        }
    }
}