<?php

namespace Alaa\Paymob\Facade;

use Alaa\Paymob\AccountInfo;
use Alaa\Paymob\EntryPayment\Auth;
use Alaa\Paymob\EntryPayment\OrderRegister;
use Alaa\Paymob\EntryPayment\PaymentKeyRequest;
use Alaa\Paymob\Exceptions\PaymentException;
use Alaa\Paymob\Methods\Kiosk;
use Alaa\Paymob\Methods\MobileWallet;
use Alaa\Paymob\Methods\OnlineCard;

class Paymob
{
    private static ?PaymentKeyRequest $paymentKeyRequest = null;


    /**
     * accountInfo is array or AccountInfo object
     * @throws PaymentException
     */
    public static function prepare($accountInfo, array $orderInfo)
    {
        if (is_array($accountInfo)) {
            $accountInfo = new AccountInfo($accountInfo);
        }
        if ($accountInfo instanceof AccountInfo) {
            $orderRegister = new OrderRegister(new Auth($accountInfo));
            $orderRegister->registrarOrder($orderInfo);
            self::$paymentKeyRequest = new PaymentKeyRequest($orderRegister);
        } else
            throw new PaymentException("accountInfo Param is not array or AccountInfo object", 100);
    }


    /**
     * @param array $params
     * @param string $iframe
     * @return array
     * @throws PaymentException
     */
    public static function payWithOnlineCard(array $params, string $iframe = ''): array
    {
        self::checkPrepare();
        return OnlineCard::getUrl($params, self::$paymentKeyRequest, $iframe);

    }


    /**
     * @param array $params
     * @param string $number
     * @return array
     * @throws PaymentException
     */
    public static function payWithMobileWallet(array $params, string $number): array
    {
        self::checkPrepare();
        return MobileWallet::pay($params, self::$paymentKeyRequest, $number);
    }


    /**
     * @param array $params
     * @return array
     * @throws PaymentException
     */
    public static function payWithKiosk(array $params): array
    {
        self::checkPrepare();
        return Kiosk::pay($params, self::$paymentKeyRequest);
    }


    /**
     * @throws PaymentException
     */
    private static function checkPrepare()
    {
        if (self::$paymentKeyRequest === null) {
            throw new PaymentException("please run " . self::class . "::prepare(\$accountInfo) first ", 104);
        }
    }

    /**
     * @return PaymentKeyRequest
     */
    public static function getPaymentKeyRequest(): PaymentKeyRequest
    {
        return self::$paymentKeyRequest;
    }

    /**
     * @param PaymentKeyRequest $paymentKeyRequest
     */
    public static function setPaymentKeyRequest(PaymentKeyRequest $paymentKeyRequest): void
    {
        self::$paymentKeyRequest = $paymentKeyRequest;
    }


}