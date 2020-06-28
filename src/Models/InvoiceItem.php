<?php

namespace Szhorvath\OperaSalesforce\Models;

use Lester\EloquentSalesForce\Model;
use Szhorvath\OperaSalesforce\Traits\SalesforceModelTrait;

class InvoiceItem extends Model
{
    use SalesforceModelTrait;

    protected $table = 'Invoice_Item__c';

    protected $readonly = [
        'Line_Total_Amount__c',
        'Product_Code__c',
    ];

    public $columns = [
        'Id',
        'Invoice__c',
        'CurrencyIsoCode',
        'Name', //text
        'Opera_Id__c',
        'Product__c',
        'Product_Code__c',
        'Quantity__c',
        'Unit_Amount__c',
        'Managing_Office__c',
        'Line_Total_Amount__c'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'Invoice__c');
    }
}
