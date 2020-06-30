<?php

namespace Szhorvath\OperaSalesforce\Models;

use Lester\EloquentSalesForce\Model;
use Szhorvath\OperaSalesforce\Models\Account;
use Szhorvath\OperaSalesforce\Models\Invoice;
use Szhorvath\OperaSalesforce\Traits\SalesforceModelTrait;

class Forecast extends Model
{
    use SalesforceModelTrait;

    protected $table = 'Forecast__c';

    protected $readonly = [
        'Account__r.ERP_Code__c',
        'Month__c',
        'Year__c',
    ];

    public $columns = [
        'Id',
        'Account__c', //Master-Detail(Account)
        'CurrencyIsoCode', //Picklist
        'Name',
        'Amount__c',
        'Managing_Office__c',
        'Invoice__c',
        'Type__c',
        'Account__r.ERP_Code__c',
        'Month__c',
        'Year__c',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'Account__c');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'Invoice__c');
    }
}
