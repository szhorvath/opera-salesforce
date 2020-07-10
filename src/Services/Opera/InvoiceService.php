<?php

namespace Szhorvath\OperaSalesforce\Services\Opera;

use Szhorvath\OperaSalesforce\Traits\OperaServiceTrait;
use Szhorvath\OperaSalesforce\Repositories\Opera\InvoiceRepository;

class InvoiceService
{
    use OperaServiceTrait;

    protected $invoiceRepository;

    protected $invoice;

    public function __construct(array $config, ?string $invoiceNumber = null, ?string $accountCode = null, ?int $invoiceId = null)
    {
        $this->invoiceRepository = new InvoiceRepository($config['source']);
        $this->locale = $config['locale'];
        $this->currency = $config['currency'];
        $this->office = $config['office'];

        if ($invoiceNumber && $accountCode) {
            $this->setInvoice($invoiceNumber, $accountCode);
        } elseif ($invoiceId) {
            $this->setInvoiceById($invoiceId);
        }
    }

    public function setInvoiceById($invoiceId)
    {
        $this->invoice = $this->invoiceRepository->findById($invoiceId);

        return $this;
    }

    public function setInvoice($invoiceNumber, $accountCode)
    {
        $this->invoice = $this->invoiceRepository->find($invoiceNumber, $accountCode);

        return $this;
    }

    public function isEmpty()
    {
        return empty($this->invoice);
    }

    public function getInvoiceNumber()
    {
        return $this->invoice->st_trref;
    }

    public function getTax()
    {
        return $this->formatNumber($this->invoice->st_vatval);
    }

    public function getAmount()
    {
        return $this->formatNumber($this->invoice->st_trvalue);
    }

    public function getDueDate()
    {
        return $this->isoDate($this->invoice->st_dueday);
    }

    public function getBalance()
    {
        if ($this->invoice->st_fcurr) {
            return $this->formatNumber($this->invoice->st_fcbal) / 100;
        }

        return $this->formatNumber($this->invoice->st_trbal);
    }

    public function getHomeBalance()
    {
        return $this->formatNumber($this->invoice->st_trbal);
    }

    public function isPaid()
    {
        return $this->getBalance() == 0 ? true : false;
    }
}
