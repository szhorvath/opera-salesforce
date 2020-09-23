<?php

namespace Szhorvath\OperaSalesforce\Repositories\Salesforce;

use Szhorvath\OperaSalesforce\Models\Product;
use Szhorvath\OperaSalesforce\Models\Pricebook;
use Szhorvath\OperaSalesforce\Models\PricebookEntry;
use Szhorvath\OperaSalesforce\Traits\SalesforceRepositoryTrait;

class ProductRepository
{
    use SalesforceRepositoryTrait;

    public function find($code)
    {
        return Product::byCode($code)->first();
    }

    public function newProduct()
    {
        return new Product;
    }

    public function newPricebookEntry()
    {
        return new PricebookEntry;
    }

    public function createPricebookEntry(array $data)
    {
        return PricebookEntry::create($data);
    }

    public function describe()
    {
        return Product::describe();
    }

    public function officePricebook()
    {
        if (!$pricebook = Pricebook::find($this->config['pricebook'])) {
            throw new \Exception("Pricebook is missing in Salesforce: " . $this->config['pricebook']);
        }

        return $pricebook;
    }

    public function basePricebook()
    {
        if (!$pricebook = Pricebook::find(config('opera_salesforce.pricebook'))) {
            throw new \Exception("Pricebook is missing in Salesforce: Standard Price Book");
        }

        return $pricebook;
    }
}
