<?php

namespace Szhorvath\OperaSalesforce\Models;

use Lester\EloquentSalesForce\Model;
use Szhorvath\OperaSalesforce\Models\Order;
use Szhorvath\OperaSalesforce\Traits\SalesforceModelTrait;

class OrderItem extends Model
{
    use SalesforceModelTrait;

    protected $table = 'OrderItem';

    protected $readonly = [
        "OriginalOrderItemId",
        "ListPrice",
        "AvailableQuantity",
        "OrderItemNumber",
        "CreatedById",
        "TotalPrice",
        "LastModifiedById",
    ];

    public $columns = [
        'Id',
        // 'OrderId',
        // 'CurrencyIsoCode', //Picklist
        'TotalPrice',
        'ListPrice',
        'Opera_Id__c', //Number(18, 0) (External ID)
        // 'Product2Id', //Lookup(Product)
        'Managing_Office__c',
        'Quantity', //Number(16, 2)
        'Description', //Text(255)
        'ServiceDate',
        // 'PricebookEntryId',
        'UnitPrice', //Currency(16, 2)
    ];

    /**
     * Scope a query to only include product of UK.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeById($query, $id)
    {
        return $query->where('Opera_Id__c', (int) $id);
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'OrderId');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'Product2Id');
    }
}
