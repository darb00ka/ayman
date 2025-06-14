<?php

namespace Alaa\Paymob\EntryPayment;

use Alaa\Paymob\AccountInfo;

abstract class Base
{
       protected AccountInfo $accountInfo ;
       public function __construct(AccountInfo $accountInfo)
       {
           $this->accountInfo = $accountInfo;
       }

    /**
     * @return AccountInfo
     */
    public function getAccountInfo(): AccountInfo
    {
        return $this->accountInfo;
    }
}