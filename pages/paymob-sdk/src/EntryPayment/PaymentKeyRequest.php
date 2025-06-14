<?php

namespace Alaa\Paymob\EntryPayment;

use Alaa\Paymob\Client;
use Alaa\Paymob\Exceptions\PaymentException;
use Alaa\Paymob\URLS;
use GuzzleHttp\Exception\GuzzleException;

class PaymentKeyRequest
{
    private OrderRegister $orderRegister;

    public function __construct(OrderRegister $orderRegister)
    {

        $this->orderRegister = $orderRegister;
    }

    /**
     * @return OrderRegister
     */
    public function getOrderRegister(): OrderRegister
    {
        return $this->orderRegister;
    }


    /**
     * @throws PaymentException
     */
    public function getPaymentToken(array $params): array
    {
        $params = $this->checkKeyRequestParams($params);
        try {
            $req = Client::requestJson("post", URLS::PaymentKey, [
                "json" => $params,
            ]);

            if (isset($req['token'])) {
                unset($params["auth_token"]);
                return array_merge($params,  $req);
            }
        } catch (GuzzleException $e) {
            throw new PaymentException($e->getMessage(), $e->getCode());
        }
        throw new PaymentException("token value Not Found Maybe Error at Paymob Server", 100);
    }


    /**
     * @param array $params
     * @return array
     * @throws PaymentException
     */
    private function checkKeyRequestParams(array $params): array
    {
        $params["auth_token"] = $this->getOrderRegister()->getAuth()->getToken();
        (!isset($params["expiration"])) && $params["expiration"] = 36000;
        if ((!isset($params["order_id"])) && ($this->getOrderRegister()->getRegisteredOrders()->count() === 0)) {
            throw new PaymentException("order_id not found and order collection count is zero please set order id or register new Order", 104);
        } elseif ((!isset($params["order_id"])) && ($this->getOrderRegister()->getRegisteredOrders()->count() > 0)) {
            $params["order_id"] = strval($this->getOrderRegister()->getRegisteredOrders()->last()["id"]);
        }
        return $params;
    }
}