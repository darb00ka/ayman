<?php

namespace Alaa\Paymob\EntryPayment;

use Alaa\Paymob\AccountInfo;
use Alaa\Paymob\Client;
use Alaa\Paymob\Exceptions\PaymentException;
use Alaa\Paymob\URLS;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;

class Auth extends Base
{

    private string $token;
    private Carbon $expireTime;

    public function __construct(AccountInfo $accountInfo)
    {
        $this->token = "";
        $this->expireTime = Carbon::yesterday("Africa/Cairo");
        parent::__construct($accountInfo);
    }


    /**
     * @throws PaymentException
     */
    public function getToken(): string
    {
        if (($this->token === "") || (!Carbon::now("Africa/Cairo")->lessThan($this->expireTime))) {
            $this->generateNewToken();
        }
        return $this->token;
    }


    /**
     * @throws PaymentException
     */
    public function generateNewToken(): string
    {
        try {
            $req = Client::requestJson("post", URLS::Auth, [
                "json" => [
                    "api_key" => $this->accountInfo->api_key
                ]
            ]);
            $this->expireTime = Carbon::now("Africa/Cairo")->addMinutes(55);
            if (isset($req['token']) && $this->token = $req['token']) {
                return  $this->token;
            }
        } catch (GuzzleException $e) {
            throw new PaymentException($e->getMessage(), $e->getCode());
        }
        throw new PaymentException("token value Not Found Maybe Error at Paymob Server", 100);
    }


}