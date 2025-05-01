<?php

namespace Saulmoralespa\WompiPa\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class CurrencyValidator extends AbstractValidator
{
    /**
     * Allowed currency
     */
    protected const ALLOWED_CURRENCY = 'USD';

    /**
     * Validates the currency in the validation subject.
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $currency = $validationSubject['currency'] ?? null;

        if ($currency !== self::ALLOWED_CURRENCY) {
            return $this->createResult(
                false,
                [__('Currency "%1" is not allowed. Only USD is accepted.', $currency)]
            );
        }

        return $this->createResult(true);
    }
}
