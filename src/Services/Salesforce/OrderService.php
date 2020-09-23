<?php

namespace Szhorvath\OperaSalesforce\Services\Salesforce;

use Illuminate\Support\Str;
use Szhorvath\OperaSalesforce\Models\Invoice;
use Szhorvath\OperaSalesforce\Repositories\Salesforce\OrderRepository;


class OrderService
{
    protected $orderRepository;

    protected $order;

    protected $officePricebookId;

    public function __construct(array $config, string $docNumber = null)
    {
        $this->orderRepository = new OrderRepository($config);
        $this->officePricebookId = $config['pricebook'];

        if ($docNumber) {
            $this->findOrder($docNumber);
        }
    }

    public function findOrder($docNumber)
    {
        $this->order = $this->orderRepository->find($docNumber);
    }

    public function getOrderItems()
    {
        return $this->isEmpty() ? collect() : $this->order->items;
    }

    public function getInvoices()
    {
        return $this->isEmpty() ? collect() : $this->order->invoices;
    }

    public function deleteForecastInvoices()
    {
        return $this->getInvoices()->each(function ($invoice) {
            if ($invoice->forecast) {
                $invoice->forecast->delete();
            }
        });
    }

    public function deleteInvoices()
    {
        return $this->getInvoices()->each(fn ($invoice) => $invoice->delete());
    }

    public function deleteOrderItems()
    {
        return $this->getOrderItems()->each(fn ($orderItem) => $orderItem->delete());
    }

    public function insertOrder(object $data)
    {
        if ($this->isEmpty()) {
            $this->order = $this->orderRepository->newOrder();
            $this->order->Name             = $data->documentNumber;
        }

        $this->order->AccountId            = $data->accountId;
        $this->order->OrderReferenceNumber = $data->salesOrderNumber;
        $this->order->Type                 = $data->type;
        $this->order->PoNumber             = $data->customerReference;
        $this->order->CurrencyIsoCode      = $data->currency;
        $this->order->EffectiveDate        = $data->startDate;
        $this->order->Delivery_Date__c     = $data->deliveryDate ?? null;
        $this->order->Delivery_Number__c   = $data->deliveryNumber ?? null;
        $this->order->Credit_Number__c     = $data->creditNumber ?? null;
        $this->order->Credit_Date__c       = $data->creditDate ?? null;
        $this->order->Invoice_Number__c    = $data->invoiceNumber ?? null;
        $this->order->Invoice_Date__c      = $data->invoiceDate ?? null;
        $this->order->Due_Date__c          = $data->dueDate;
        $this->order->Vat__c               = $data->vat;
        $this->order->Managing_Office__c   = $data->managingOffice;
        $this->order->Raised_By__c         = $data->raisedBy;
        $this->order->Description          = $data->documentNumber;
        $this->order->ShippingStreet       = $data->shippingStreet;
        $this->order->ShippingCity         = $data->shippingCity;
        $this->order->ShippingState        = $data->shippingState;
        $this->order->ShippingPostalCode   = $data->shippingPostalCode;
        $this->order->Pricebook2Id         = $this->officePricebookId;
        $this->order->Status_Code__c       = $data->statusCode;
        $this->order->Status               = 'Quote';

        $this->order = $this->order->save();

        return $this->order;
    }

    public function updateStatus($status)
    {
        if ($this->order->Status !== $status) {
            $this->order->Status = $status;
        }

        return $this->order->save();
    }

    public function insertOrderItem(object $data)
    {
        $orderItem = $this->getOrderItems()->filter(fn ($item) => $item->Opera_Id__c == $data->id)->first();

        if (!$orderItem) {
            $orderItem = $this->orderRepository->newOrderItem();

            $orderItem->OrderId          = $data->orderId;
            $orderItem->Product2Id       = $data->productId;
            $orderItem->PricebookEntryId = $data->pricebookEntryId;
        }

        $orderItem->Opera_Id__c        = (int) $data->id;
        $orderItem->Description        = Str::limit($data->memo, 253);
        $orderItem->Quantity           = $data->quantity;
        $orderItem->UnitPrice          = (float) $data->unitPrice;
        $orderItem->Managing_Office__c = $data->managingOffice;

        return $orderItem->save();
    }

    public function insertInvoice(object $data)
    {
        $invoice = $this->orderRepository->newInvoice();

        $invoice->Order__c            = $data->orderId;
        $invoice->Account__c          = $data->accountId;
        $invoice->Credit_Number__c    = $data->creditNumber ?? null;
        $invoice->Credit_Date__c      = $data->creditDate ?? null;
        $invoice->CurrencyIsoCode     = $data->currency;
        $invoice->Delivery_Date__c    = $data->deliveryDate ?? null;
        $invoice->Delivery_Number__c  = $data->deliveryNumber ?? null;
        $invoice->Document_Number__c  = $data->documentNumber;
        $invoice->Managing_Office__c  = $data->managingOffice;
        $invoice->Invoice_Amount__c   = $data->invoiceAmount ?? null;
        $invoice->Invoice_Balance__c  = $data->invoiceBalance ?? null;
        $invoice->Invoice_Date__c     = $data->invoiceDate ?? null;
        $invoice->Invoice_Due_Date__c = $data->invoiceDueDate ?? null;
        $invoice->Invoice_Number__c   = $data->invoiceNumber ?? null;
        $invoice->Invoice_Tax__c      = $data->invoiceTax ?? null;
        $invoice->Paid__c             = $data->paid ?? false;
        $invoice->Name                = $data->reference;
        $invoice->Start_Date__c       = $data->startDate;
        $invoice->Status__c           = $data->status;

        return $invoice->save();
    }

    public function insertInvoiceItem(object $data)
    {
        $invoiceItem = $this->orderRepository->newInvoiceItem();

        $invoiceItem->Opera_Id__c        = $data->operaId;
        $invoiceItem->Invoice__c         = $data->invoiceId;
        $invoiceItem->CurrencyIsoCode    = $data->currency;
        $invoiceItem->Managing_Office__c = $data->managingOffice;
        $invoiceItem->Product__c         = $data->productId;
        $invoiceItem->Quantity__c        = $data->quantity;
        $invoiceItem->Unit_Amount__c     = $data->unitAmount;
        $invoiceItem->Name               = $data->productName;

        return $invoiceItem->save();
    }

    public function insertForecast(Invoice $invoice, $accountId)
    {
        $invoice = Invoice::find($invoice->Id);

        $forecast = $this->orderRepository->newForecast();
        $forecast->Account__c         = $accountId;
        $forecast->Invoice__c         = $invoice->Id;
        $forecast->Name               = $invoice->Name;
        $forecast->Amount__c          = $invoice->Total_Amount__c;
        $forecast->Managing_Office__c = $invoice->Managing_Office__c;
        $forecast->Date__c            = $invoice->Start_Date__c;
        $forecast->CurrencyIsoCode    = $invoice->CurrencyIsoCode;
        $forecast->Type__c            = 'Actual';

        return $forecast->save();
    }

    public function describe()
    {
        return $this->orderRepository->describe();
    }

    public function deleteOrder()
    {
        $this->updateStatus('Order');
        $deleted = $this->order->delete();
        $this->order = null;
        return $deleted;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function isEmpty()
    {
        return empty($this->order);
    }
}
