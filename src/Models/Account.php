<?php

namespace Szhorvath\OperaSalesforce\Models;

use Lester\EloquentSalesForce\Model;
use Szhorvath\OperaSalesforce\Models\Order;
use Szhorvath\OperaSalesforce\Traits\SalesforceModelTrait;

class Account extends Model
{
    use SalesforceModelTrait;

    protected $readonly = [
        'BillingAddress',
    ];

    public $columns = [
        "Id",
        "ERP_Code__c",
        "Industry__c",
        "Account_Type__c",
        "Competitor_Account__c",
        "Managing_Office__c",
        "CurrencyIsoCode",
        'AccountNumber',
        "Name",
        "Phone",
        "BillingAddress",
        "BillingStreet",
        "BillingCity",
        "BillingState",
        "BillingPostalCode",
        "BillingCountry",
        "Website",
        "OwnerId"
    ];

    public function scopeByCode($query, $code)
    {
        return $query->whereRaw("ERP_Code__c = '$code'");
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'AccountId');
    }
}
