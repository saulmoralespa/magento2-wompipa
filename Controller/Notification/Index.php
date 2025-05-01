<?php

namespace Saulmoralespa\WompiPa\Controller\Notification;

use Exception;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order;
use Saulmoralespa\WompiPa\Controller\Index as WompiPaIndex;

class Index extends WompiPaIndex implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * Execute the action.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $content = $this->request->getContent();
        $payload = json_decode($content, true);

        if (empty($payload['data'] ?? null)) {
            $this->logger->error('WompiPa Notification: Invalid payload');
            return $this->createJsonResponse(false, 'Invalid payload');
        }
        $transactionId = $payload['data']['transaction']['id'];
        $status = $payload['data']['transaction']['status'];
        $reference = $payload['data']['transaction']['reference'];
        $amountInCents = $payload['data']['transaction']['amount_in_cents'];
        $signature = $payload['signature']['checksum'];
        $timestamp = $payload['timestamp'];

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $sign = $this->wompiApi->getSignature($transactionId, $status, $amountInCents, $timestamp, $storeId);
            if ($sign !== $signature) {
                $this->logger->error('WompiPa Notification: Invalid signature');
                return $this->createJsonResponse(false, 'Invalid signature');
            }
            $order = $this->loadByIncrementId($reference);
            $comment = __(
                'Payment approved by WompiPa. Transaction ID:: %transactionId',
                ['transactionId' => $transactionId]
            );

            if ($order->getState() === Order::STATE_PROCESSING) {
                $this->logger->info('WompiPa Notification: Order already processed');
                return $this->createJsonResponse(true, 'Order already processed');
            }

            $payment = $order->getPayment();
            $transaction = $this->transactionRepository->getByTransactionType(
                TransactionInterface::TYPE_ORDER,
                $payment->getId()
            );
            switch ($status) {
                case 'APPROVED':
                    $payment->setIsTransactionClosed(1);
                    $payment->setIsTransactionPending(false);
                    $payment->setIsTransactionApproved(true);
                    $payment->setSkipOrderProcessing(false);
                    $payment->addTransactionCommentsToOrder($transaction, $comment);
                    $order->addCommentToStatusHistory($comment);
                    $order->setState(Order::STATE_PROCESSING);
                    $order->setStatus(Order::STATE_PROCESSING);
                    $invoice = $this->invoiceService->prepareInvoice($order);
                    $invoice->setTransactionId($transactionId)
                        ->register()
                        ->pay()
                        ->save();
                    break;
                case 'VOIDED':
                case 'DECLINED':
                case 'ERROR':
                    $payment->setIsTransactionClosed(1);
                    $payment->setIsTransactionPending(false);
                    $payment->setIsTransactionDenied(true);
                    $payment->setSkipOrderProcessing(true);
                    $payment->setIsTransactionDenied(true);
                    $message = __(
                        'Payment denied by WompiPa. Transaction ID:: %transactionId',
                        ['transactionId' => $transactionId]
                    );
                    $payment->addTransactionCommentsToOrder($transaction, $message);
                    $order->addCommentToStatusHistory($message);
                    $order->setState(Order::STATE_CANCELED);
                    $order->setStatus(Order::STATE_CANCELED);
                    break;
            }

            $this->orderRepository->save($order);
        } catch (Exception $exception) {
            $this->logger->error('WompiPa Notification: ' . $exception->getMessage());
            return $this->createJsonResponse(false, 'Error processing notification');
        }

        return $this->createJsonResponse(true, 'Notification processed successfully');
    }

    /**
     * Create Csrf Validation Exception.
     *
     * @param RequestInterface $request Required by interface, not used.
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Validate For Csrf.
     *
     * @param RequestInterface $request
     *
     * @return bool true
     */
    public function validateForCsrf(RequestInterface $request): bool
    {
        return true;
    }

    /**
     * Create JSON response.
     *
     * @param bool $success
     * @param string $message
     * @return ResultInterface
     */
    private function createJsonResponse(bool $success, string $message): ResultInterface
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData([
            'success' => $success,
            'message' => $message,
        ]);
        return $result;
    }
}
