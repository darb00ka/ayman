<?php

namespace Alaa\Paymob;

use Alaa\Paymob\Exceptions\PaymentException;

/**
 * @property  $api_key
 * @property  $iframe
 * @property  $HMAC
 */

class AccountInfo
{
    private  array $info  ;

    /**
     * Account Info
     * apiKey =>
     * HMAC =>
     *
     * @param array $infos
     * @throws PaymentException
     */
    public function __construct(array $infos )
    {
        $this->info = [
            'api_key' => "" ,
            'HMAC' => "" ,
            "iframe" => ""
        ];
        $this->checkInfo($infos);
    }


    /**
     * @throws PaymentException
     */
    private function checkInfo(array $infos){
        foreach ( $this->info as  $info => $value ){
            if(isset($infos[$info])){
                $this->info[$info] = $infos[$info] ;
            }else{
                throw new PaymentException("$info not Found please set it " , 104);
            }
        }
    }


    public function __get($name)
    {
        return $this->info[$name]  ??  null;
    }

    public function __set($name, $value)
    {
        $this->info[$name] = $value;
    }
}