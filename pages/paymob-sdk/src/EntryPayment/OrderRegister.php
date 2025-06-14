<?php

namespace Alaa\Paymob\EntryPayment;

use Alaa\Paymob\Client;
use Alaa\Paymob\Exceptions\PaymentException;
use Alaa\Paymob\URLS;
use GuzzleHttp\Exception\GuzzleException;
use Tightenco\Collect\Support\Collection;

class OrderRegister
{
    private Auth $auth ;
    private Collection $orders ;
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
        $this->orders = collect();

    }

    /**
     * @return Auth
     */
    public function getAuth(): Auth
    {
        return $this->auth;
    }


    /**
     * @throws PaymentException
     */
    public function registrarOrder(array $orderInfo){

        $orderInfo["auth_token"] = $this->getAuth()->getToken();
        try {
            $this->orders->add( Client::requestJson( "post" , URLS::OrderRegistration, [
                "json" => $orderInfo
            ]));
            return $this->orders->last();
        } catch (GuzzleException $e) {
            throw new PaymentException($e->getMessage(), $e->getCode());
        }
    }


    /**
     * @return Collection
     */
    public function getRegisteredOrders(): Collection
    {
        return $this->orders;
    }

}