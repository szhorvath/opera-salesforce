<?php

namespace Szhorvath\OperaSalesforce\Services\Salesforce;

use Szhorvath\OperaSalesforce\Repositories\Salesforce\InvoiceRepository;


class InvoiceService
{
    protected $invoiceRepository;

    protected $invoice = null;

    public function __construct(array $config, ?string $number = null)
    {
        $this->invoiceRepository = new InvoiceRepository($config);

        if ($number) {
            $this->findInvoice($number);
        }
    }

    public function findInvoice($number)
    {
        $this->invoice = $this->invoiceRepository->find($number);
    }

    public function updateInvoice(object $data)
    {
        if (!$this->invoice) {
            throw new \Exception("Invoice not found in Salesforce. Invoice number: $data->invoiceNumber");
        };

        $this->invoice->Invoice_Amount__c   = $data->invoiceAmount;
        $this->invoice->Invoice_Balance__c  = $data->invoiceBalance;
        $this->invoice->Invoice_Tax__c      = $data->invoiceTax;
        $this->invoice->Invoice_Due_Date__c = $data->invoiceDueDate ?? null;
        $this->invoice->Paid__c             = $data->paid ?? false;

        return $this->invoice->save();
    }
}
