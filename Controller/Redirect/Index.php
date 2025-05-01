<?php

namespace Saulmoralespa\WompiPa\Controller\Redirect;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index implements HttpGetActionInterface
{

    /**
     * @param Session $checkoutSession
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        private readonly Session $checkoutSession,
        private readonly ResultFactory $resultFactory
    ) {
    }

    /**
     * Execute the action.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();

        $additionalInfo = $payment->getAdditionalInformation();
        $fields = $additionalInfo['wompi_post_params'] ?? [];
        $url = $additionalInfo['wompi_redirect_url'] ?? '';

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
            'action' => $url,
            'fields' => $fields
        ]);
    }
}
