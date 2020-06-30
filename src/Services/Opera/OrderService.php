<?php

namespace Szhorvath\OperaSalesforce\Services\Opera;

use Szhorvath\OperaSalesforce\Repositories\Opera\OrderRepository;
use Szhorvath\OperaSalesforce\Traits\OperaServiceTrait;

class OrderService
{
    use OperaServiceTrait;

    protected $orderRepository;

    protected $order;

    public function __construct(array $config, string $docNumber = null)
    {
        $this->orderRepository = new OrderRepository($config['source']);
        $this->locale = $config['locale'];
        $this->currency = $config['currency'];
        $this->office = $config['office'];

        if ($docNumber) {
            $this->setOrder($docNumber);
        }
    }

    public function setOrder($docNumber)
    {
        $this->order = $this->orderRepository->find($docNumber);
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function isEmpty()
    {
        return empty($this->order);
    }

    public function getDocumentNumber()
    {
        return $this->order->ih_doc;
    }

    public function getSalesOrderNumber()
    {
        return $this->order->ih_sorder;
    }

    public function getCustomerReference()
    {
        return $this->order->ih_custref;
    }

    public function getAccountCode()
    {
        return $this->order->ih_account ?? null;
    }

    public function getAccountName()
    {
        return $this->order->ih_name;
    }

    public function getVat()
    {
        return $this->formatNumber($this->order->ih_vat);
    }

    public function getCurrency()
    {
        return $this->order->ih_fcurr ?: $this->currency;
    }

    public function getShippingStreet()
    {
        $line1 = $this->order->ih_delad1 ? $this->order->ih_delad1 . ', ' : '';
        $line2 = $this->order->ih_delad2 ? $this->order->ih_delad2 . ', ' : '';
        $line3 = $this->order->ih_delad3 ? $this->order->ih_delad3 : '';
        return $line1 . $line2 . $line3;
    }

    public function getShippingCity()
    {
        return $this->order->ih_delad4;
    }

    public function getShippingState()
    {
        return $this->order->ih_delad5;
    }

    public function getShippingPostalCode()
    {
        return $this->order->ih_deladpc;
    }

    public function getStartDate()
    {
        return $this->isoDate($this->order->ih_date);
    }

    public function getDueDate()
    {
        return $this->isoDate($this->order->ih_due);
    }

    public function getDeliveryDate()
    {
        return $this->isoDate($this->order->ih_deldate);
    }

    public function getInvoiceDate()
    {
        return $this->isoDate($this->order->ih_invdate);
    }

    public function getRaisedBy()
    {
        return $this->order->ih_raised;
    }

    public function getStatus()
    {
        return $this->getOrderStatus($this->order->ih_docstat);
    }

    public function getStatusCode()
    {
        return $this->order->ih_docstat;
    }

    public function isCreditNote()
    {
        return $this->order->ih_docstat === 'C';
    }

    public function getCreditDate()
    {
        return $this->isoDate($this->order->ih_credate);
    }

    public function getCreditNumber()
    {
        return $this->order->ih_credit;
    }

    public function getDeliveryNumber()
    {
        return $this->order->ih_deliv;
    }

    public function getInvoiceNumber()
    {
        return $this->order->ih_invoice;
    }
}
