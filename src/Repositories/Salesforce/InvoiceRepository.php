<?php

namespace Szhorvath\OperaSalesforce\Repositories\Salesforce;

use Szhorvath\OperaSalesforce\Models\Invoice;
use Szhorvath\OperaSalesforce\Traits\SalesforceRepositoryTrait;

class InvoiceRepository
{
    use SalesforceRepositoryTrait;

    public function find($number)
    {
        return Invoice::office($this->config['office'])->byNumber($number)->first();
    }

    public function newInvoice()
    {
        return new Invoice;
    }

    public function describe()
    {
        return Invoice::describe();
    }
}
