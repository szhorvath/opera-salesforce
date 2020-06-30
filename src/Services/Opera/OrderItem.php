<?php

namespace Szhorvath\OperaSalesforce\Services\Opera;

use Illuminate\Support\Str;
use Szhorvath\OperaSalesforce\Traits\OperaServiceTrait;

class OrderItem
{
    use OperaServiceTrait;

    protected $item;

    public function __construct($item, $currency, $office)
    {
        $this->office = $office;
        $this->currency = $currency;
        $this->item = $item;
    }

    public function isA()
    {
        return $this->item->it_status === 'A' && !is_null($this->item->it_stock);
    }

    public function isX()
    {
        return $this->item->it_status === 'X' && !is_null($this->item->it_stock);
    }

    public function getInvoiceReference()
    {
        return $this->item->it_numinv ?: $this->item->it_numdelv;
    }

    public function getProductCode()
    {
        return $this->item->it_stock;
    }

    public function getQuantity()
    {
        return $this->getOperaQuantity($this->item->it_quan, $this->item->cf_dps);
    }

    public function getUnitPrice()
    {
        return ((float) $this->item->it_exvat / 100) / $this->getQuantity();
    }

    public function getCurrency()
    {
        return $this->item->it_fcurr ?: $this->currency;
    }

    public function getId()
    {
        return (int) $this->item->id;
    }

    public function getMemo()
    {
        return $this->item->it_memo;
    }

    public function getDocumentNumber()
    {
        return $this->item->it_doc;
    }

    public function getDeliveryNumber()
    {
        return $this->item->it_numdelv;
    }

    public function getInvoiceNumber()
    {
        return $this->item->it_numinv;
    }

    public function getDeliveryDate()
    {
        return $this->isoDate($this->item->it_dtedelv);
    }

    public function getStartDate()
    {
        return $this->isoDate($this->item->it_date);
    }

    public function getInvoiceDate()
    {
        return $this->isoDate($this->item->it_dteinv);
    }

    public function isRebate()
    {
        return Str::contains(strtolower($this->item->it_desc), 'rebate');
    }

    public function getProductName()
    {
        return $this->item->cn_desc;
    }

    public function getProductDescription()
    {
        $extra = isset($this->item->cn_exten) ? ' - ' . $this->item->cn_exten : '';

        return $this->item->cn_desc . $extra;
    }

    public function getProductFamilyName()
    {
        return $this->getProductFamily($this->item->cn_catag);
    }

    public function getProductUnit()
    {
        return 'Each';
    }

    public function getProductType()
    {
        return 'Undefined';
    }
}
