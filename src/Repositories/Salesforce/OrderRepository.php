<?php

namespace Szhorvath\OperaSalesforce\Repositories\Salesforce;

use Szhorvath\OperaSalesforce\Models\Forecast;
use Szhorvath\OperaSalesforce\Models\Order;
use Szhorvath\OperaSalesforce\Models\Invoice;
use Szhorvath\OperaSalesforce\Models\InvoiceItem;
use Szhorvath\OperaSalesforce\Models\OrderItem;
use Szhorvath\OperaSalesforce\Traits\SalesforceRepositoryTrait;

class OrderRepository
{
    use SalesforceRepositoryTrait;

    public function find($docNumber)
    {
        return Order::office($this->config['office'])->byDocNumber($docNumber)->first();
    }

    public function newOrder()
    {
        return new Order;
    }

    public function describe()
    {
        return Order::describe();
    }

    public function newOrderItem()
    {
        return new OrderItem;
    }

    public function newInvoice()
    {
        return new Invoice;
    }

    public function findInvoiceByNumber($number)
    {
        return Invoice::byNumber($number)->first();
    }

    public function newInvoiceItem()
    {
        return new InvoiceItem();
    }

    public function newForecast()
    {
        return new Forecast();
    }
}
