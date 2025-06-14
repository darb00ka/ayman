<?php

use Alaa\Paymob\AccountInfo;
use Alaa\Paymob\EntryPayment\Auth;
use Alaa\Paymob\Exceptions\PaymentException;
use Alaa\Paymob\Methods\Kiosk;
use Alaa\Paymob\Methods\MobileWallet;

require_once './vendor/autoload.php';


$accountInfo = [
    "api_key" => "ZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKSVV6VXhNaUo5LmV5SndjbTltYVd4bFgzQnJJam94TXpVd05UWXNJbU5zWVhOeklqb2lUV1Z5WTJoaGJuUWlMQ0p1WVcxbElqb2lhVzVwZEdsaGJDSjkuWDZtT19uNlg3bEdmdy1kQW40QlZpQV91UTcwMlNqeVRFSmJuQmN0RWlqVmFEbjN5dnhNeXAxQS1vVWxZU0lyTVRTWF83aHZkTlZ4OVdDcXFydVJfN2c=",
    "HMAC" => "",
    "iframe" => "304625",

];


try {


    $auth = new Auth(new AccountInfo($accountInfo));
    $auth->getToken();
    $orderReg = new \Alaa\Paymob\EntryPayment\OrderRegister($auth);
    $orderReg->registrarOrder(["delivery_needed" => false,
        "amount_cents" => "1000",
        "currency" => "EGP",
        "items" => []
    ]);
    $paymentKey = new \Alaa\Paymob\EntryPayment\PaymentKeyRequest($orderReg);



    $kiosk = \Alaa\Paymob\Methods\OnlineCard::getUrl([
        "amount_cents" => "1000",
        "currency" => "EGP",
        "billing_data" => [
            "apartment" => "803",
            "email" => "claudette09@exa.com",
            "floor" => "42",
            "first_name" => "Clifford",
            "street" => "Ethan Land",
            "building" => "8028",
            "phone_number" => "+86(8)9135210487",
            "shipping_method" => "PKG",
            "postal_code" => "01898",
            "city" => "Jaskolskiburgh",
            "country" => "CR",
            "last_name" => "Nicolas",
            "state" => "Utah"
        ],
        "integration_id" => 1211440
    ] , $paymentKey  );
    var_dump($kiosk);
//    Paymob::prepare($accountInfo, [
//        "delivery_needed" => false,
//        "amount_cents" => "1000",
//        "currency" => "EGP",
//        "items" => []
//    ]);
//
//    var_dump(Paymob::payWithKiosk([
//        "amount_cents" => "100",
//        "currency" => "EGP",
//        "billing_data" => [
//            "apartment" => "803",
//            "email" => "claudette09@exa.com",
//            "floor" => "42",
//            "first_name" => "Clifford",
//            "street" => "Ethan Land",
//            "building" => "8028",
//            "phone_number" => "+86(8)9135210487",
//            "shipping_method" => "PKG",
//            "postal_code" => "01898",
//            "city" => "Jaskolskiburgh",
//            "country" => "CR",
//            "last_name" => "Nicolas",
//            "state" => "Utah"
//        ],
//        "integration_id" => 1211452
//    ]));
} catch (PaymentException $e) {
    var_dump($e->getMessage(), $e->getFile(), $e->getLine());
}
