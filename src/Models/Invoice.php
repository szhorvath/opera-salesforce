<?php

namespace Szhorvath\OperaSalesforce\Models;

use Lester\EloquentSalesForce\Model;
use Szhorvath\OperaSalesforce\Traits\SalesforceModelTrait;

class Invoice extends Model
{
    use SalesforceModelTrait;

    protected $table = 'Invoice__c';

    protected $readonly = [
        'Total_Amount__c',
    ];

    public $columns = [
        'Id',
        'Account__c',
        'Credit_Number__c',
        'Credit_Date__c',
        'CurrencyIsoCode', //Picklist
        'Delivery_Date__c', //Date
        'Document_Number__c', //Text(80) (External ID) (Unique Case Insensitive)
        'Managing_Office__c',
        'Invoice_Amount__c',
        'Invoice_Balance__c',
        'Invoice_Date__c', //Date
        'Invoice_Due_Date__c', //Date
        'Name', //text
        'Invoice_Number__c',
        'Delivery_Number__c',
        'Invoice_Tax__c',
        'Paid__c',
        'Start_Date__c', //Date
        'Status__c', //Picklist
        'Total_Amount__c', //Roll-Up Summary (SUM Real Order Item)
    ];


    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'Invoice_Item__c');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'Order__c');
    }

    public function forecast()
    {
        return $this->hasOne(Forecast::class, 'Invoice__c');
    }

    public function scopeByNumber($query, $number)
    {
        return $query->where('Invoice_Number__c', $number);
    }
}
