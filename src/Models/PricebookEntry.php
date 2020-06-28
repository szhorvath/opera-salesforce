<?php

namespace Szhorvath\OperaSalesforce\Models;

use Lester\EloquentSalesForce\Model;
use Szhorvath\OperaSalesforce\Models\Product;
use Szhorvath\OperaSalesforce\Models\Pricebook;

class PricebookEntry extends Model
{

    protected $readonly = [];

    public $columns = [
        "Id",
        'IsActive', //Checkbox
        'IsDeleted', //Checkbox
        'IsArchived', //Checkbox
        "CreatedById", //Lookup(User)
        "CurrencyIsoCode", //Picklist
        'LastModifiedById', //Lookup(User)
        "UnitPrice", //Currency(16, 2)
        "Pricebook2Id", //Lookup(Price Book)
        "Product2Id", //Lookup(Product)
        "ProductCode", //Text(255)
        "UseStandardPrice", //Checkbox
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function pricebook()
    {
        return $this->belongsTo(Pricebook::class, 'Pricebook2Id', 'Id');
    }
}
