<?php

namespace Szhorvath\OperaSalesforce\Services\Salesforce;

use Szhorvath\OperaSalesforce\Repositories\Salesforce\ProductRepository;


class ProductService
{
    protected $productRepository;

    protected $product;

    protected $basePricebookId;

    protected $officePricebookId;

    public function __construct(array $config, string $productCode = null)
    {
        $this->productRepository = new ProductRepository($config);

        if ($productCode) {
            $this->findProduct($productCode);
            $this->setBasePricebookId();
            $this->setOfficePricebookId();
        }
    }

    public function findProduct($productCode)
    {
        $this->product = $this->productRepository->find($productCode);
    }

    public function describe()
    {
        return $this->productRepository->describe();
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function isEmpty()
    {
        return empty($this->product);
    }

    public function createProduct(object $data)
    {
        $this->product = $this->productRepository->newProduct();

        $this->product->IsActive           = true;
        $this->product->ProductCode        = $data->productCode;
        $this->product->CurrencyIsoCode    = $data->currency;
        $this->product->Description        = $data->description;
        $this->product->Family             = $data->family;
        $this->product->Name               = $data->name;
        $this->product->Managing_Office__c = $data->managingOffice;
        $this->product->Unit__c            = $data->unit;
        $this->product->Types__c           = $data->type;

        return $this->product->save();
    }

    public function setBasePricebookId()
    {
        $this->basePricebookId = $this->productRepository->basePricebook()->Id;
    }

    public function setOfficePricebookId()
    {
        $this->officePricebookId = $this->productRepository->officePricebook()->Id;
    }

    public function getBasePricebookEntry($currency)
    {
        return $this->product->pricebookEntries
            ->filter(fn ($item) => $item->Pricebook2Id === $this->basePricebookId && $item->CurrencyIsoCode === $currency)
            ->first();
    }

    public function getOfficePricebookEntry($currency)
    {
        return $this->product->pricebookEntries
            ->filter(fn ($item) => $item->Pricebook2Id === $this->officePricebookId && $item->CurrencyIsoCode === $currency)
            ->first();
    }

    public function getPricebookEntryId($unitPrice, $currency)
    {
        if (!$this->getBasePricebookEntry($currency)) {
            $this->productRepository->createPricebookEntry([
                'IsActive' => true,
                'CurrencyIsoCode' => $currency,
                'UnitPrice' => 0,
                'Pricebook2Id' => $this->basePricebookId,
                'Product2Id' => $this->product->Id,
            ]);
        }

        if (!$officePricebookEntry = $this->getOfficePricebookEntry($currency)) {
            $officePricebookEntry = $this->productRepository->createPricebookEntry([
                'IsActive' => true,
                'CurrencyIsoCode' => $currency,
                'UnitPrice' => $unitPrice,
                'Pricebook2Id' => $this->officePricebookId,
                'Product2Id' => $this->product->Id,
                'UseStandardPrice' => false,
            ]);
        }

        return $officePricebookEntry->Id;
    }
}
