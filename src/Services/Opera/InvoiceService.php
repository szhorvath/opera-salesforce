<?php

namespace Szhorvath\OperaSalesforce\Services\Opera;

use Szhorvath\OperaSalesforce\Traits\OperaServiceTrait;
use Szhorvath\OperaSalesforce\Repositories\Opera\InvoiceRepository;

class InvoiceService
{
    use OperaServiceTrait;

    protected $invoiceRepository;

    protected $invoice;

    public function __construct(array $config, string $invoiceNumber = null, string $accountCode = null)
    {
        $this->invoiceRepository = new InvoiceRepository($config['source']);
        $this->locale = $config['locale'];
        $this->currency = $config['currency'];
        $this->office = $config['office'];

        if ($invoiceNumber && $accountCode) {
            $this->setInvoice($invoiceNumber, $accountCode);
        }
    }

    public function setInvoice($invoiceNumber, $accountCode)
    {
        $this->invoice = $this->invoiceRepository->find($invoiceNumber, $accountCode);

        return $this;
    }

    public function getInvoiceNumber()
    {
        return $this->invoice->st_trref;
    }

    public function getTax()
    {
        return $this->invoice->st_vatval;
    }

    public function getAmount()
    {
        return $this->invoice->st_trvalue;
    }

    public function getDueDate()
    {
        return $this->isoDate($this->invoice->st_dueday);
    }

    public function getBalance()
    {
        if ($this->invoice->st_fcurr) {
            return (float) $this->invoice->st_fcbal / 100;
        }

        return (float) $this->invoice->st_trbal;
    }

    public function isPaid()
    {
        return $this->getBalance() == 0 ? true : false;
    }
}
