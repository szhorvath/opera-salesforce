<?php

namespace Szhorvath\OperaSalesforce\Models;

use Lester\EloquentSalesForce\Model;
use Szhorvath\OperaSalesforce\Models\Account;
use Szhorvath\OperaSalesforce\Models\Invoice;
use Szhorvath\OperaSalesforce\Models\OrderItem;
use Szhorvath\OperaSalesforce\Traits\SalesforceModelTrait;

class Order extends Model
{
    use SalesforceModelTrait;

    protected $table = 'Order';

    protected $readonly = [
        'Total_Amount__c',
        'ShippingAddress',
    ];

    public $columns = [
        'Id',
        'Name',
        'AccountId', //Lookup(Account)
        'CurrencyIsoCode',
        'PoNumber',
        'Delivery_Date__c',
        'Invoice_Date__c',
        'Credit_Number__c',
        'Due_Date__c',
        'Raised_By__c',
        'Vat__c',
        'EffectiveDate',
        'Description',
        'OrderReferenceNumber',
        'Managing_Office__c',
        'Type',
        'ShippingAddress',
        'ShippingStreet',
        'ShippingCity',
        'ShippingState',
        'ShippingPostalCode',
        'Status',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountId');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'OrderId');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'Order__c');
    }

    public function scopeByDocNumber($query, $docNumber)
    {
        return $query->where('Name', $docNumber);
    }
}
