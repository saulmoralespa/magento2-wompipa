<?php

namespace Saulmoralespa\WompiPa\Gateway\Request;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Saulmoralespa\WompiPa\Gateway\Config\WompiPa;

class InitializeRequest implements BuilderInterface
{

    /**
     * @param WompiPa $config
     * @param SubjectReader $subjectReader
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        protected WompiPa $config,
        protected SubjectReader $subjectReader,
        protected UrlInterface $urlBuilder
    ) {
    }

    /**
     * Builds the request for the payment gateway.
     *
     * @param array $buildSubject The subject containing the payment data object.
     * @return array An array containing the request data.
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order     = $paymentDO->getOrder();
        $reference = $order->getOrderIncrementId();
        $billing = $order->getBillingAddress();
        $storeId   = $order->getStoreId();
        $amount    = $order->getGrandTotalAmount();
        $amountInCents = (int)($amount * 100);
        $currency = $order->getCurrencyCode();
        $signature = "{$reference}{$amountInCents}{$currency}";
        $signature .= $this->config->getIntegrityKey($storeId);
        $signatureIntegrity = hash('sha256', $signature);

        return [
            'public-key' => $this->config->getPublicKey($storeId),
            'currency' => $currency,
            'amount-in-cents' => $amountInCents,
            'reference' => $reference,
            'redirect-url' => $this->urlBuilder->getUrl('wompipa/return/index', ['_secure' => true]),
            'signature:integrity' => $signatureIntegrity,
            'customer-data:email' => $billing?->getEmail(),
            'customer-data:full-name' => trim($billing?->getFirstname() . ' ' . $billing?->getLastname()),
            'customer-data:phone-number' => $billing?->getTelephone()
        ];
    }
}
