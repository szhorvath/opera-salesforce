<?php

namespace Szhorvath\OperaSalesforce\Models;

use Lester\EloquentSalesForce\Model;
use Szhorvath\OperaSalesforce\Models\InvoiceItem;
use Szhorvath\OperaSalesforce\Models\PricebookEntry;
use Szhorvath\OperaSalesforce\Traits\SalesforceModelTrait;

class Product extends Model
{
    use SalesforceModelTrait;

    protected $table = 'Product2';

    protected $readonly = [
        'CreatedById',
        'LastModifiedById',
    ];

    public $columns = [
        "Id",
        "IsActive", //Checkbox
        "CreatedById", //Lookup(User)
        "DisplayUrl", //URL(1000)
        "ExternalDataSourceId", //Lookup(External Data Source)
        "ExternalId", //Text(255)
        'LastModifiedById', //Lookup(User)
        "Lead__c", //Lookup(Lead)
        "ProductCode", //Text(255)
        "CurrencyIsoCode", //Picklist
        "Description", //Text Area(4000)
        "Family", //Picklist
        "Name", //Text(255)
        "Managing_Office__c", //Picklist
        "StockKeepingUnit", //Text(180)
        "QuantityUnitOfMeasure", //Picklist
        "Sub_Product_Family__c", //Picklist
        "Unit__c", //Text(255),
        "Types__c", //Picklist (Multi-Select)
        "Group__c", //Picklist
    ];

    public function pricebookEntries()
    {
        return $this->hasMany(PricebookEntry::class, 'Product2Id', 'Id');
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }


    /**
     * Scope a query to only include product of certain code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed $code
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCode($query, $code)
    {
        return $query->whereRaw("ProductCode = '$code'");
    }
}
