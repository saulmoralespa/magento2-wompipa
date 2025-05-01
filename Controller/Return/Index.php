<?php

namespace Saulmoralespa\WompiPa\Controller\Return;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order;
use Saulmoralespa\WompiPa\Controller\Index as WompiPaIndex;

class Index extends WompiPaIndex implements HttpGetActionInterface
{

    /**
     * Execute the action.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $transactionId = $this->request->getParam('id');

        if (!$transactionId) {
            $this->logger->error('WompiPa Return: order_id missing');
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('checkout/cart');
            return $resultRedirect;
        }

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $response = $this->wompiApi->get($transactionId, $storeId);

            if (empty($response['data'] ?? null)) {
                throw new LocalizedException(__('WompiPa Return: Invalid response'));
            }

            $transactionId = $response['data']['id'];
            $status = $response['data']['status'];
            $reference = $response['data']['reference'];
            $order = $this->loadByIncrementId($reference);
            $payment = $order->getPayment();
            $transaction = $this->transactionRepository->getByTransactionType(
                TransactionInterface::TYPE_ORDER,
                $payment->getEntityId()
            );
            $comment = __(
                'Payment approved by WompiPa. Transaction ID:: %transactionId',
                ['transactionId' => $transactionId]
            );

            if ($order->getState() === Order::STATE_PROCESSING) {
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('checkout/cart');
                return $resultRedirect;
            }

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
                    $pathRedirect = "checkout/onepage/success";
                    break;
                case 'PENDING':
                    $pathRedirect = "wompipa/payment/pending";
                    break;
                default:
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
                    $pathRedirect = "checkout/onepage/failure";
                    break;
            }

            $this->orderRepository->save($order);

        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            $pathRedirect = "checkout/onepage/failure";
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($pathRedirect);
        return $resultRedirect;
    }
}
