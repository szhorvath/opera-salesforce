<?php

namespace Szhorvath\OperaSalesforce\Repositories\Salesforce;

use Szhorvath\OperaSalesforce\Models\OrderItem;
use Szhorvath\OperaSalesforce\Traits\SalesforceRepositoryTrait;

class OrderItemRepository
{
    use SalesforceRepositoryTrait;

    public function find($id)
    {
        return OrderItem::office($this->config['office'])->byId($id)->first();
    }

    public function newOrderItem()
    {
        return new OrderItem;
    }

    public function describe()
    {
        return OrderItem::describe();
    }
}
