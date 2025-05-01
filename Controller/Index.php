<?php

namespace Saulmoralespa\WompiPa\Controller;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\StoreManagerInterface;
use Saulmoralespa\WompiPa\Logger\Logger;
use Saulmoralespa\WompiPa\Model\WompiApi;

abstract class Index
{
    /**
     * @param RequestInterface $request
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResultFactory $resultFactory
     * @param StoreManagerInterface $storeManager
     * @param OrderRepositoryInterface $orderRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param InvoiceService $invoiceService
     * @param WompiApi $wompiApi
     * @param Logger $logger
     */
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        protected readonly ResultFactory $resultFactory,
        protected readonly StoreManagerInterface $storeManager,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly TransactionRepositoryInterface $transactionRepository,
        protected readonly InvoiceService $invoiceService,
        protected readonly WompiApi $wompiApi,
        protected readonly Logger $logger
    ) {
    }

    /**
     * Load Order by Increment ID
     *
     * @param string $incrementId
     * @return Order|null
     */
    public function loadByIncrementId(string $incrementId): ?Order
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();

        $ordersList = $this->orderRepository->getList($searchCriteria);

        $orders = $ordersList->getItems();

        if (empty($orders)) {
            return null;
        }

        /** @var Order $order */
        $order = reset($orders);

        return $order;
    }
}
