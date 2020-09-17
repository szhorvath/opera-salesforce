<?php

namespace Szhorvath\OperaSalesforce\Services\Opera;

use Szhorvath\OperaSalesforce\Traits\OperaServiceTrait;
use Szhorvath\OperaSalesforce\Repositories\Opera\InvoiceRepository;

class InvoiceService
{
    use OperaServiceTrait;

    protected $invoiceRepository;

    protected $invoice = null;

    protected $invoiceNumber;

    protected $accountCode;

    protected $invoiceId;

    public function __construct(array $config = [], ?string $invoiceNumber = null, ?string $accountCode = null, ?int $invoiceId = null)
    {
        if (!empty($config)) {
            $this->setInvoiceRepository($config['source']);
            $this->locale = $config['locale'];
            $this->currency = $config['currency'];
            $this->office = $config['office'];
            $this->invoiceNumber = $invoiceNumber;
            $this->accountCode = $accountCode;
            $this->invoiceId = $invoiceId;
            $this->invoice = $this->getInvoice();
        }
    }

    public function setInvoiceRepository($source)
    {
        $this->invoiceRepository = new InvoiceRepository($source);

        return $this;
    }

    public function getInvoice()
    {
        if ($this->invoiceNumber && $this->accountCode) {
            return $this->invoiceRepository->find($this->invoiceNumber, $this->accountCode);
        } elseif ($this->invoiceId) {
            return $this->invoiceRepository->findById($this->invoiceId);
        }

        return null;
    }

    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

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
