<?php

namespace Szhorvath\OperaSalesforce;

use Illuminate\Support\Str;
use Szhorvath\OperaSalesforce\Models\Order;
use Szhorvath\OperaSalesforce\Models\Invoice;
use Szhorvath\OperaSalesforce\Services\Opera\OrderItem;
use Szhorvath\OperaSalesforce\Services\Opera\OrderService as OperaOrderService;
use Szhorvath\OperaSalesforce\Services\Opera\ProductService as OperaProductService;
use Szhorvath\OperaSalesforce\Services\Opera\InvoiceService as OperaInvoiceService;
use Szhorvath\OperaSalesforce\Services\Opera\OrderItemService as OperaOrderItemService;
use Szhorvath\OperaSalesforce\Services\Salesforce\OrderService as SalesforceOrderService;
use Szhorvath\OperaSalesforce\Services\Salesforce\AccountService as SalesforceAccountService;
use Szhorvath\OperaSalesforce\Services\Salesforce\InvoiceService as SalesforceInvoiceService;
use Szhorvath\OperaSalesforce\Services\Salesforce\ProductService as SalesforceProductService;

class OperaSalesforce
{
    protected $config = [];

    protected $docNumber = null;

    protected $operaInvoice = null;

    protected $operaOrderService;

    protected $operaOrderItemService;

    protected $operaInvoiceService;

    protected $salesforceOrderService;

    protected $salesforceAccountService;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function init($docNumber)
    {
        $this->setDocNumber($docNumber)
            ->setOperaOrderService()
            ->setOperaOrderItemService()
            ->setSalesforceOrderService()
            ->setSalesforceAccountService();

        return $this;
    }

    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function setDocNumber($docNumber)
    {
        $this->docNumber = (string) $docNumber;
        return $this;
    }

    public function setOperaOrderService()
    {
        $this->operaOrderService = new OperaOrderService($this->config, $this->docNumber);
        return $this;
    }

    public function setOperaOrderItemService()
    {
        $this->operaOrderItemService = new OperaOrderItemService($this->config, $this->docNumber);
        return $this;
    }

    public function setSalesforceOrderService()
    {
        $this->salesforceOrderService = new SalesforceOrderService($this->config, $this->docNumber);
        return $this;
    }

    public function setSalesforceAccountService()
    {
        $this->salesforceAccountService = new SalesforceAccountService($this->config, $this->operaOrderService->getAccountCode());
        return $this;
    }

    public function initOperaInvoiceService(?string $invoiceNumber, ?string $accountCode, ?int $invoiceId = null)
    {
        $this->operaInvoiceService = new OperaInvoiceService($this->config, $invoiceNumber, $accountCode, $invoiceId);
        return $this;
    }

    public function setOperaInvoiceService(OperaInvoiceService $operaInvoiceInstance)
    {
        $this->operaInvoiceService = $operaInvoiceInstance;
        return $this;
    }

    public function getOperaOrder()
    {
        return $this->operaOrderService->getOrder();
    }

    public function getSalesforceOrder()
    {
        return $this->salesforceOrderService->getOrder();
    }

    public function operaOrderExists()
    {
        return !$this->operaOrderService->isEmpty();
    }

    public function salesforceOrderExists()
    {
        return !$this->salesforceOrderService->isEmpty();
    }

    public function deleteSalesforceOrder()
    {
        return $this->salesforceOrderService->deleteOrder();
    }

    public function syncSalesforceWithOpera()
    {
        if ($this->operaOrderService->isCreditNote()) {
            $this->processCreditNote();
            return $this;
        }
        try {
            //Insert Order
            $salesforceOrder = $this->insertOrder();
        } catch (\Exception $exception) {
            if (Str::contains($exception->getMessage(), 'cannot change pricebook on order with order items')) {
                $this->salesforceOrderService->findOrder($this->operaOrderService->getDocumentNumber());
                //Delete Invoices and Forecasts
                $this->salesforceOrderService->deleteForecastInvoices();
                $this->salesforceOrderService->deleteInvoices();
                //Delete Order
                $this->salesforceOrderService->deleteOrder();

                //Recreate order
                $salesforceOrder = $this->insertOrder();
            } else {
                throw new \Exception(json_encode([
                    'docNumber' => $this->operaOrderService->getDocumentNumber(),
                    'division' => $this->operaOrderService->getManagingOffice(),
                    'message' => $exception->getMessage()
                ]));
            }
        }


        //Create order items
        $this->operaOrderItemService->getAItems()
            ->each(fn ($item) => $this->createSalesforceOrderItem($item, $salesforceOrder));

        //Update order status
        $this->salesforceOrderService->updateStatus($this->operaOrderService->getStatus());

        //Delete Invoices and Forecasts
        $this->salesforceOrderService->deleteForecastInvoices();
        $this->salesforceOrderService->deleteInvoices();

        //Create invoices
        $this->operaOrderItemService->getDeliveries()
            ->map(fn ($operaItems) => $this->createInvoice($operaItems, $salesforceOrder))
            ->each(fn ($invoice) => $this->createForecast($invoice));

        return $this;
    }

    public function insertOrder()
    {
        return $this->salesforceOrderService->insertOrder((object) [
            'accountId' => $this->salesforceAccountService->getAccountId(),
            'salesOrderNumber' => $this->operaOrderService->getSalesOrderNumber(),
            'customerReference' => $this->operaOrderService->getCustomerReference(),
            'currency' => $this->operaOrderService->getCurrency(),
            'startDate' => $this->operaOrderService->getStartDate(),
            'deliveryDate' => $this->operaOrderService->getDeliveryDate(),
            'deliveryNumber' => $this->operaOrderService->getDeliveryNumber(),
            'invoiceDate' => $this->operaOrderService->getInvoiceDate(),
            'invoiceNumber' => $this->operaOrderService->getInvoiceNumber(),
            'dueDate' => $this->operaOrderService->getDueDate(),
            'vat' => $this->operaOrderService->getVat(),
            'managingOffice' => $this->operaOrderService->getManagingOffice(),
            'raisedBy' => $this->operaOrderService->getRaisedBy(),
            'documentNumber' => $this->operaOrderService->getDocumentNumber(),
            'shippingStreet' => $this->operaOrderService->getShippingStreet(),
            'shippingCity' => $this->operaOrderService->getShippingCity(),
            'shippingState' => $this->operaOrderService->getShippingState(),
            'shippingPostalCode' => $this->operaOrderService->getShippingPostalCode(),
            'statusCode' => $this->operaOrderService->getStatusCode(),
            'type' => 'Order',
        ]);
    }

    public function createSalesforceOrderItem(OrderItem $operaItem, Order $salesforceOrder)
    {
        $salseforceProductService = new SalesforceProductService($this->config, $operaItem->getProductCode());

        $salesforceProduct = $this->insertProduct($operaItem, $salseforceProductService);

        $pricebookEntryId = $salseforceProductService->getPricebookEntryId($operaItem->getUnitPrice(), $operaItem->getCurrency());

        $salseforceOrderItem = $this->salesforceOrderService->insertOrderItem((object) [
            'orderId'          => $salesforceOrder->Id,
            'productId'        => $salesforceProduct->Id,
            'pricebookEntryId' => $pricebookEntryId,
            'id'               => $operaItem->getId(),
            'memo'             => $operaItem->getMemo(),
            'quantity'         => $operaItem->getQuantity(),
            'unitPrice'        => $operaItem->getUnitPrice(),
            'managingOffice'   => $operaItem->getManagingOffice(),
        ]);

        return $salseforceOrderItem;
    }

    public function getOperaInvoiceService($invoiceNumber, $accountCode)
    {
        return new OperaInvoiceService($this->config, $invoiceNumber, $accountCode);
    }

    public function createInvoice($operaItems, Order $salesforceOrder)
    {
        $operaDelivery = $operaItems->first();

        $data = collect([
            'orderId'        => $salesforceOrder->Id,
            'accountId'      => $this->salesforceAccountService->getAccountId(),
            'currency'       => $operaDelivery->getCurrency(),
            'deliveryNumber' => $operaDelivery->getDeliveryNumber(),
            'deliveryDate'   => $operaDelivery->getDeliveryDate(),
            'documentNumber' => $operaDelivery->getDocumentNumber(),
            'managingOffice' => $operaDelivery->getManagingOffice(),
            'reference'      => $operaDelivery->getInvoiceReference(),
            'startDate'      => $operaDelivery->getInvoiceDate() ?: $operaDelivery->getDeliveryDate(),
            'invoiceDate'    => $operaDelivery->getInvoiceDate(),
            'status'         => $operaDelivery->getInvoiceDate() ? 'Invoice' : 'Delivery',
        ]);



        if ($operaDelivery->getInvoiceNumber()) {
            $operaInvoiceService = $this->getOperaInvoiceService($operaDelivery->getInvoiceNumber(), $this->operaOrderService->getAccountCode());

            $data = $data->merge([
                'invoiceAmount'  => $operaInvoiceService->getAmount(),
                'invoiceBalance' => $operaInvoiceService->getHomeBalance(),
                'invoiceDueDate' => $operaInvoiceService->getDueDate(),
                'invoiceNumber'  => $operaInvoiceService->getInvoiceNumber(),
                'invoiceTax'     => $operaInvoiceService->getTax(),
                'paid'           => $operaInvoiceService->isPaid(),
            ]);
        }

        $salesforceInvoice = $this->salesforceOrderService->insertInvoice((object) $data->toArray());

        $operaItems->each(fn ($item) => $this->createInvoiceItem($item, $salesforceInvoice));

        return $salesforceInvoice;
    }

    public function updateInvoice()
    {
        if ($this->operaInvoiceService->isEmpty()) {
            return;
        }

        $salesforceInvoiceService = new SalesforceInvoiceService($this->config, $this->operaInvoiceService->getInvoiceNumber());
        return $salesforceInvoiceService->updateInvoice((object) [
            'invoiceNumber'  => $this->operaInvoiceService->getInvoiceNumber(),
            'invoiceAmount'  => $this->operaInvoiceService->getAmount(),
            'invoiceBalance' => $this->operaInvoiceService->getHomeBalance(),
            'invoiceDueDate' => $this->operaInvoiceService->getDueDate(),
            'invoiceTax'     => $this->operaInvoiceService->getTax(),
            'paid'           => $this->operaInvoiceService->isPaid(),
        ]);
    }

    public function createInvoiceItem(OrderItem $operaItem, Invoice $salesforceInvoice)
    {
        $salseforceProductService = new SalesforceProductService($this->config, $operaItem->getProductCode());

        $this->salesforceOrderService->insertInvoiceItem((object) [
            'operaId'        => $operaItem->getId(),
            'invoiceId'      => $salesforceInvoice->Id,
            'currency'       => $operaItem->getCurrency(),
            'managingOffice' => $operaItem->getManagingOffice(),
            'productId'      => $salseforceProductService->getProduct()->Id,
            'quantity'       => $operaItem->getQuantity(),
            'unitAmount'     => $operaItem->getUnitPrice(),
            'productName'    => $operaItem->getProductName(),
        ]);
    }

    public function insertProduct(OrderItem $operaItem, SalesforceProductService $salseforceProductService)
    {
        return $salseforceProductService->insertProduct((object) [
            'productCode'    => $operaItem->getProductCode(),
            'currency'       => $operaItem->getCurrency(),
            'description'    => $operaItem->getProductDescription(),
            'family'         => $operaItem->getProductFamilyName(),
            'name'           => $operaItem->getProductName(),
            'managingOffice' => $operaItem->getManagingOffice(),
            'unit'           => $operaItem->getProductUnit(),
            'weight'         => $operaItem->getProductWeight(),
            'weightUnit'     => $operaItem->getProductWeightUnit(),
            'type'           => $operaItem->getProductType(),
        ]);
    }

    public function syncProductWithSalesforce(string $productCode)
    {
        $operaProductService = new OperaProductService($this->config, $productCode);
        $salseforceProductService = new SalesforceProductService($this->config, $productCode);

        return $salseforceProductService->insertProduct((object) [
            'productCode'    => $operaProductService->getProductCode(),
            'currency'       => $operaProductService->getCurrency(),
            'description'    => $operaProductService->getDescription(),
            'family'         => $operaProductService->getFamily(),
            'name'           => $operaProductService->getName(),
            'managingOffice' => $operaProductService->getManagingOffice(),
            'unit'           => $operaProductService->getUnit(),
            'weight'         => $operaProductService->getWeight(),
            'weightUnit'     => $operaProductService->getWeightUnit(),
            'type'           => $operaProductService->getType(),
        ]);
    }


    public function processCreditNote()
    {
        if ($this->operaOrderItemService->isRebate()) {
            return;
        }

        $salesforceOrder = $this->salesforceOrderService->insertOrder((object) [
            'accountId' => $this->salesforceAccountService->getAccountId(),
            'salesOrderNumber' => $this->operaOrderService->getSalesOrderNumber(),
            'customerReference' => $this->operaOrderService->getCustomerReference(),
            'currency' => $this->operaOrderService->getCurrency(),
            'startDate' => $this->operaOrderService->getCreditDate() ?: $this->operaOrderService->getStartDate(),
            'deliveryDate' => $this->operaOrderService->getDeliveryDate(),
            'invoiceDate' => $this->operaOrderService->getInvoiceDate(),
            'creditNumber' => $this->operaOrderService->getCreditNumber(),
            'creditDate' => $this->operaOrderService->getCreditDate(),
            'dueDate' => $this->operaOrderService->getDueDate(),
            'vat' => $this->operaOrderService->getVat(),
            'managingOffice' => $this->operaOrderService->getManagingOffice(),
            'raisedBy' => $this->operaOrderService->getRaisedBy(),
            'documentNumber' => $this->operaOrderService->getDocumentNumber(),
            'shippingStreet' => $this->operaOrderService->getShippingStreet(),
            'shippingCity' => $this->operaOrderService->getShippingCity(),
            'shippingState' => $this->operaOrderService->getShippingState(),
            'shippingPostalCode' => $this->operaOrderService->getShippingPostalCode(),
            'statusCode' => $this->operaOrderService->getStatusCode(),
            'type' => 'Order',
        ]);

        //Delete Credit Note Invoices and Forecasts
        $this->salesforceOrderService->deleteForecastInvoices();
        $this->salesforceOrderService->deleteInvoices();

        $orderItems = $this->operaOrderItemService->getAItems();
        // Create Credit Note Invoice
        $salesforceCreditNoteInvoice = $this->createCreditNoteInvoice($orderItems->first(), $salesforceOrder);

        //Create Credit Note Order Items
        $orderItems->each(fn ($item) => $this->createCreditNoteOrderAndInvoiceItem($item, $salesforceOrder, $salesforceCreditNoteInvoice));

        //Create Forecast
        $this->createForecast($salesforceCreditNoteInvoice);

        //Update Credit Note Order Status
        $this->salesforceOrderService->updateStatus($this->operaOrderService->getStatus());
    }

    public function createCreditNoteInvoice(OrderItem $operaItem, Order $salesforceOrder)
    {
        $data = collect([
            'orderId'        => $salesforceOrder->Id,
            'accountId'      => $this->salesforceAccountService->getAccountId(),
            'currency'       => $operaItem->getCurrency(),
            'deliveryNumber' => $operaItem->getDeliveryNumber(),
            'deliveryDate'   => $operaItem->getDeliveryDate(),
            'documentNumber' => $operaItem->getDocumentNumber(),
            'managingOffice' => $operaItem->getManagingOffice(),
            'reference'      => $this->operaOrderService->getCreditNumber(),
            'creditNumber'   => $this->operaOrderService->getCreditNumber(),
            'creditDate'     => $this->operaOrderService->getCreditDate(),
            'startDate'      => $operaItem->getStartDate(),
            'invoiceDate'    => $operaItem->getInvoiceDate(),
            'status'         => 'Credit Note',
        ]);

        if ($operaItem->getInvoiceNumber()) {
            $operaInvoiceService = $this->getOperaInvoiceService($operaItem->getInvoiceNumber(), $this->operaOrderService->getAccountCode());

            $data = $data->merge([
                'invoiceAmount'  => $operaInvoiceService->getAmount(),
                'invoiceBalance' => $operaInvoiceService->getHomeBalance(),
                'invoiceDueDate' => $operaInvoiceService->getDueDate(),
                'invoiceNumber'  => $operaInvoiceService->getInvoiceNumber(),
                'invoiceTax'     => $operaInvoiceService->getTax(),
                'paid'           => $operaInvoiceService->isPaid(),
            ]);
        }

        return $this->salesforceOrderService->insertInvoice((object)$data->toArray());
    }

    public function createCreditNoteOrderAndInvoiceItem(OrderItem $operaItem, Order $salesforceOrder, Invoice $salesforceInvoice)
    {
        $salseforceProductService = new SalesforceProductService($this->config, $operaItem->getProductCode());

        //Get Product
        $salesforceProduct = $this->insertProduct($operaItem, $salseforceProductService);

        $unitPrice = -abs($operaItem->getUnitPrice());

        //Get Pricebook Id
        $pricebookEntryId = $salseforceProductService->getPricebookEntryId($unitPrice, $operaItem->getCurrency());

        //Create Credit Note Order Item
        $this->salesforceOrderService->insertOrderItem((object) [
            'orderId'          => $salesforceOrder->Id,
            'productId'        => $salesforceProduct->Id,
            'pricebookEntryId' => $pricebookEntryId,
            'id'               => $operaItem->getId(),
            'memo'             => $operaItem->getMemo(),
            'quantity'         => $operaItem->getQuantity(),
            'unitPrice'        => $unitPrice,
            'managingOffice'   => $operaItem->getManagingOffice(),
        ]);

        $this->salesforceOrderService->insertInvoiceItem((object) [
            'operaId'        => $operaItem->getId(),
            'invoiceId'      => $salesforceInvoice->Id,
            'currency'       => $operaItem->getCurrency(),
            'managingOffice' => $operaItem->getManagingOffice(),
            'productId'      => $salseforceProductService->getProduct()->Id,
            'quantity'       => $operaItem->getQuantity(),
            'unitAmount'     => $unitPrice,
            'productName'    => $operaItem->getProductName(),
        ]);
    }

    public function createForecast(Invoice $salesforceInvoice)
    {
        return $this->salesforceOrderService->InsertForecast($salesforceInvoice, $this->salesforceAccountService->getAccountId());
    }
}
