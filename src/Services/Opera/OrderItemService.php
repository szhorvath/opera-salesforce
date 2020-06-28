<?php

namespace Szhorvath\OperaSalesforce\Services\Opera;

use Szhorvath\OperaSalesforce\Services\Opera\OrderItem;
use Szhorvath\OperaSalesforce\Traits\OperaServiceTrait;
use Szhorvath\OperaSalesforce\Repositories\Opera\OrderItemRepository;

class OrderItemService
{
    use OperaServiceTrait;

    protected $orderItemRepository;

    protected $orderItems;

    public function __construct(array $config, string $docNumber = null)
    {
        $this->orderItemRepository = new OrderItemRepository($config['source']);
        $this->locale = $config['locale'];
        $this->currency = $config['currency'];
        $this->office = $config['office'];

        if ($docNumber) {
            $this->setOrderItems($docNumber);
        }
    }

    public function setOrderItems($docNumber)
    {
        $this->orderItems = $this->orderItemRepository
            ->getByDocNumber($docNumber)
            ->map(fn ($item) => $this->newOrderItemInstance($item));
    }

    public function getItem($id)
    {
        return $this->orderItems->filter(fn ($item) => $item->getId() === $id)->first();
    }

    public function newOrderItemInstance($item)
    {
        return new OrderItem($item, $this->currency, $this->office);
    }

    public function getAItems()
    {
        return $this->orderItems->filter(fn ($item) => $item->isA());
    }

    public function getXItems()
    {
        return $this->orderItems->filter(fn ($item) => $item->isX());
    }

    public function getDeliveries()
    {
        return $this->getXItems()->groupBy(fn ($item) => $item->getDeliveryNumber());
    }

    public function isRebate()
    {
        return $this->orderItems->filter(fn ($item) => $item->isRebate())->isNotEmpty();
    }
}
