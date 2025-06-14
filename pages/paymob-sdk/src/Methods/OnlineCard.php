<?php

namespace Alaa\Paymob\Methods;

use Alaa\Paymob\EntryPayment\PaymentKeyRequest;
use Alaa\Paymob\Exceptions\PaymentException;
use Alaa\Paymob\URLS;

class OnlineCard
{

    /**
     * @param array $params
     * @param PaymentKeyRequest $paymentKeyRequest
     * @param string $iframe
     * @return array
     * @throws PaymentException
     */
    public static function getUrl(array $params, PaymentKeyRequest $paymentKeyRequest, string $iframe = ''): array
    {
        if (($iframe !== '') || (($iframe = $paymentKeyRequest->getOrderRegister()->getAuth()->getAccountInfo()->iframe))) {
            $response = $paymentKeyRequest->getPaymentToken($params);
            $response['redirect_url'] = URLS::Base . URLS::CardIframe . $iframe . "?payment_token=" . $response['token'];
            return $response;
        }
        throw new PaymentException("Please set Iframe as parameter or in AccountInfo Object", 104);
    }

}