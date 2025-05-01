<?php

namespace Saulmoralespa\WompiPa\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\Config as BaseConfig;
use Magento\Store\Model\ScopeInterface;

class WompiPa extends BaseConfig
{
    public const CODE = 'wompipa';
    public const ACTIVE = 'active';
    public const ENVIRONMENT = 'environment';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $methodCode
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = self::CODE
    ) {
        parent::__construct($scopeConfig, $methodCode);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     *  Check if the payment method is active for a given store.
     *
     * @param int|null $storeId The store ID to check. Defaults to null.
     * @return bool True if the payment method is active, false otherwise.
     */
    public function isActive(int $storeId = null): bool
    {
        $pathPattern = 'payment/%s/%s';

        return (bool) $this->scopeConfig->getValue(
            sprintf($pathPattern, self::CODE, self::ACTIVE),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if the payment method is in test mode for a given store.
     *
     * @param int|null $storeId The store ID to check. Defaults to null.
     * @return bool The value of the configuration path, or null if not set.
     */
    public function isTest(int $storeId = null): bool
    {
        $pathPattern = 'payment/%s/%s';

        return (bool) $this->scopeConfig->getValue(
            sprintf($pathPattern, self::CODE, self::ENVIRONMENT),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get the public key for the payment method.
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function getPublicKey(int $storeId = null): ?string
    {
        return $this->getEnvironmentConfig('public_key', $storeId);
    }

    /**
     * Get the private key for the payment method.
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function getPrivateKey(int $storeId = null): ?string
    {
        return $this->getEnvironmentConfig('private_key', $storeId);
    }

    /**
     * Get the events key for the payment method.
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function getEventsKey(int $storeId = null): ?string
    {
        return $this->getEnvironmentConfig('events_key', $storeId);
    }

    /**
     * Get the integrity key for the payment method.
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function getIntegrityKey(int $storeId = null): ?string
    {
        return $this->getEnvironmentConfig('integrity_key', $storeId);
    }

    /**
     * Get the private key for the payment method.
     *
     * @param string $field
     * @param int|null $storeId
     * @return string|null
     */
    protected function getEnvironmentConfig(string $field, int $storeId = null): ?string
    {
        $environment = $this->isTest($storeId) ? 'development' : 'production';
        $group = "environment_g";
        $pathPattern = 'payment/%s/%s/%s/%s';

        return $this->scopeConfig->getValue(
            sprintf($pathPattern, self::CODE, $group, $environment, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
