<?php

namespace Szhorvath\OperaSalesforce\Repositories\Salesforce;

use Szhorvath\OperaSalesforce\Traits\SalesforceRepositoryTrait;

class InvoiceRepository
{
    use SalesforceRepositoryTrait;

    public function find($code)
    {
        return Invoice::office($this->config['office'])->byCode($code)->first();
    }

    public function getHolding()
    {
        return Account::office($this->config['office'])->byCode('holding')->first();
    }

    public function newAccount()
    {
        return new Account;
    }

    public function describe()
    {
        return Account::describe();
    }
}
