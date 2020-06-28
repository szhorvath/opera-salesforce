<?php

namespace Szhorvath\OperaSalesforce\Services\Opera;

use Szhorvath\OperaSalesforce\Traits\OperaServiceTrait;
use Szhorvath\OperaSalesforce\Repositories\Opera\ProductRepository;

class ProductService
{
    use OperaServiceTrait;

    protected $productRepository;

    protected $product;

    public function __construct(array $config, string $productCode = null)
    {
        $this->productRepository = new ProductRepository($config['source']);
        $this->locale = $config['locale'];
        $this->currency = $config['currency'];
        $this->office = $config['office'];

        if ($productCode) {
            $this->setProduct($productCode);
        }
    }

    public function setProduct($productCode)
    {
        $this->product = $this->productRepository->find($productCode);

        return $this;
    }

    public function getName()
    {
        return $this->product->cn_desc;
    }

    public function getDescription()
    {
        $extra = isset($this->product->cn_exten) ? ' - ' . $this->product->cn_exten : '';

        return $this->product->cn_desc . $extra;
    }

    public function getFamily()
    {
        return $this->getProductFamily($this->product->cn_catag);
    }

    public function getUnit()
    {
        return 'Each';
    }

    public function getType()
    {
        return 'Undefined';
    }

    public function getCurrency()
    {
        return $this->currency;
    }
}