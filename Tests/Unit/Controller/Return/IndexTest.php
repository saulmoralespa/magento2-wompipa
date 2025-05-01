<?php

namespace Saulmoralespa\WompiPa\Test\Unit\Controller\Return;

use Exception;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Saulmoralespa\WompiPa\Controller\Return\Index;
use Saulmoralespa\WompiPa\Logger\Logger;
use Saulmoralespa\WompiPa\Model\WompiApi;



class IndexTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var TransactionRepositoryInterface|MockObject
     */
    private $transactionRepositoryMock;

    /**
     * @var InvoiceService|MockObject
     */
    private $invoiceServiceMock;

    /**
     * @var WompiApi|MockObject
     */
    private $wompiApiMock;

    /**
     * @var Logger|MockObject
     */
    private $loggerMock;

    /**
     * @var Redirect|MockObject
     */
    private $redirectMock;

    /**
     * @var Index
     */
    private $controller;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->transactionRepositoryMock = $this->createMock(TransactionRepositoryInterface::class);
        $this->invoiceServiceMock = $this->createMock(InvoiceService::class);
        $this->wompiApiMock = $this->createMock(WompiApi::class);
        $this->loggerMock = $this->createMock(Logger::class);

        $this->redirectMock = $this->createMock(Redirect::class);

        $this->resultFactoryMock->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->redirectMock);

        $this->controller = new Index(
            $this->requestMock,
            $this->searchCriteriaBuilderMock,
            $this->resultFactoryMock,
            $this->storeManagerMock,
            $this->orderRepositoryMock,
            $this->transactionRepositoryMock,
            $this->invoiceServiceMock,
            $this->wompiApiMock,
            $this->loggerMock
        );
    }

    public function testExecuteWithMissingTransactionId(): void
    {
        $this->requestMock->method('getParam')
            ->with('id')
            ->willReturn(null);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('WompiPa Return: order_id missing');

        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('checkout/cart')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->redirectMock, $result);
    }

    public function testExecuteWithInvalidResponse(): void
    {
        $transactionId = 'txn-123';

        $this->requestMock->method('getParam')
            ->with('id')
            ->willReturn($transactionId);

        $storeId = 1;
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->method('getId')->willReturn($storeId);

        $this->storeManagerMock->method('getStore')
            ->willReturn($storeMock);

        // Empty or invalid API response
        $this->wompiApiMock->method('get')
            ->with($transactionId, $storeId)
            ->willReturn([]);

        $this->loggerMock->expects($this->once())
            ->method('error');

        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('checkout/onepage/failure')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->redirectMock, $result);
    }

    public function testExecuteWithException(): void
    {
        $transactionId = 'txn-123';

        $this->requestMock->method('getParam')
            ->with('id')
            ->willReturn($transactionId);

        $storeId = 1;
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->method('getId')->willReturn($storeId);

        $this->storeManagerMock->method('getStore')
            ->willReturn($storeMock);

        // Simulate an exception during API call
        $this->wompiApiMock->method('get')
            ->with($transactionId, $storeId)
            ->willThrowException(new Exception('API Error'));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('API Error');

        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('checkout/onepage/failure')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->redirectMock, $result);
    }
}
