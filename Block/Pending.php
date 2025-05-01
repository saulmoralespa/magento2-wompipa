<?php

namespace Saulmoralespa\WompiPa\Block;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Phrase;

class Pending extends Template
{

    /**
     * Get message
     *
     * @return Phrase
     */
    public function getMessage(): Phrase
    {
        return __('The status of the order is pending, waiting to process the payment by Wompi.');
    }

    /**
     * Get url home
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getUrlHome(): string
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}
