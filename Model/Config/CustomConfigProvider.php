<?php

namespace Saulmoralespa\WompiPa\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository;
use Saulmoralespa\WompiPa\Gateway\Config\WompiPa;

class CustomConfigProvider implements ConfigProviderInterface
{

    /**
     * Payment method code
     * @var string
     */
    protected const CODE = WompiPa::CODE;

    /**
     * @param Repository $assetRepo
     */
    public function __construct(
        protected Repository $assetRepo
    ) {
    }

    /**
     * Get the configuration for the payment method
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'logoUrl' => $this->assetRepo->getUrl("Saulmoralespa_WompiPa::images/logo.svg")
                ],
            ],
        ];
    }
}
