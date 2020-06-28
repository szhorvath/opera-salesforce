<?php

namespace Szhorvath\OperaSalesforce\Models;

use Lester\EloquentSalesForce\Model;
use Szhorvath\OperaSalesforce\Models\PricebookEntry;

class Pricebook extends Model
{
    protected $table = 'Pricebook2';

    protected $readonly = [];

    public $columns = [
        "Id",
        'IsActive', //Checkbox
        "CreatedById", //Lookup(User)
        "Description", //Text(255)
        "IsStandard", //Checkbox
        "LastModifiedById", //Lookup(User)
        "Name", //Text(255)
    ];

    public function pricebookEntries()
    {
        return $this->hasMany(PricebookEntry::class, 'Pricebook2Id', 'Id');
    }
}
