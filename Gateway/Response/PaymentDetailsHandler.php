<?php

namespace Saulmoralespa\WompiPa\Gateway\Response;

use InvalidArgumentException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;

class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * Handles the response from the payment gateway.
     *
     * @param array $handlingSubject The subject containing the payment data object.
     * @param array $response The response from the payment gateway.
     * @throws InvalidArgumentException If the payment data object is not provided.
     */
    public function handle(array $handlingSubject, array $response): void
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $handlingSubject['payment'];
        $payment = $paymentDO->getPayment();

        $payment->setAdditionalInformation('wompi_post_params', $response['redirect_form_params']);
        $payment->setAdditionalInformation('wompi_redirect_url', $response['redirect_url']);
    }
}
