<?php
declare(strict_types=1);

namespace Saulmoralespa\WompiPa\Test\Unit\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Saulmoralespa\WompiPa\Gateway\Config\WompiPa;

class WompiPaTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var WompiPa
     */
    private $config;

    /**
     * @var int
     */
    private $storeId = 1;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->config = new WompiPa($this->scopeConfigMock);
    }

    public function testIsActiveReturnsTrueWhenEnabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/' . WompiPa::CODE . '/' . WompiPa::ACTIVE,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
            ->willReturn('1');

        $this->assertTrue($this->config->isActive($this->storeId));
    }

    public function testIsActiveReturnsFalseWhenDisabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/' . WompiPa::CODE . '/' . WompiPa::ACTIVE,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
            ->willReturn('0');

        $this->assertFalse($this->config->isActive($this->storeId));
    }

    public function testIsTestReturnsTrueWhenInTestMode(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/' . WompiPa::CODE . '/' . WompiPa::ENVIRONMENT,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
            ->willReturn('1');

        $this->assertTrue($this->config->isTest($this->storeId));
    }

    public function testIsTestReturnsFalseWhenInProductionMode(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/' . WompiPa::CODE . '/' . WompiPa::ENVIRONMENT,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
            ->willReturn('0');

        $this->assertFalse($this->config->isTest($this->storeId));
    }

    public function testGetPublicKeyInDevelopmentMode(): void
    {
        $expectedKey = 'test_public_key_123';

        $this->scopeConfigMock->method('getValue')
            ->willReturnMap([
                [
                    'payment/' . WompiPa::CODE . '/' . WompiPa::ENVIRONMENT,
                    ScopeInterface::SCOPE_STORE,
                    $this->storeId,
                    '1'
                ],
                [
                    'payment/' . WompiPa::CODE . '/environment_g/development/public_key',
                    ScopeInterface::SCOPE_STORE,
                    $this->storeId,
                    $expectedKey
                ]
            ]);

        $this->assertEquals($expectedKey, $this->config->getPublicKey($this->storeId));
    }

    public function testGetPublicKeyInProductionMode(): void
    {
        $expectedKey = 'prod_public_key_456';

        $this->scopeConfigMock->method('getValue')
            ->willReturnMap([
                [
                    'payment/' . WompiPa::CODE . '/' . WompiPa::ENVIRONMENT,
                    ScopeInterface::SCOPE_STORE,
                    $this->storeId,
                    '0'
                ],
                [
                    'payment/' . WompiPa::CODE . '/environment_g/production/public_key',
                    ScopeInterface::SCOPE_STORE,
                    $this->storeId,
                    $expectedKey
                ]
            ]);

        $this->assertEquals($expectedKey, $this->config->getPublicKey($this->storeId));
    }

    public function testGetPrivateKeyInDevelopmentMode(): void
    {
        $expectedKey = 'test_private_key_123';

        $this->scopeConfigMock->method('getValue')
            ->willReturnMap([
                [
                    'payment/' . WompiPa::CODE . '/' . WompiPa::ENVIRONMENT,
                    ScopeInterface::SCOPE_STORE,
                    $this->storeId,
                    '1'
                ],
                [
                    'payment/' . WompiPa::CODE . '/environment_g/development/private_key',
                    ScopeInterface::SCOPE_STORE,
                    $this->storeId,
                    $expectedKey
                ]
            ]);

        $this->assertEquals($expectedKey, $this->config->getPrivateKey($this->storeId));
    }

    public function testGetEventsKeyInDevelopmentMode(): void
    {
        $expectedKey = 'test_events_key_123';

        $this->scopeConfigMock->method('getValue')
            ->willReturnMap([
                [
                    'payment/' . WompiPa::CODE . '/' . WompiPa::ENVIRONMENT,
                    ScopeInterface::SCOPE_STORE,
                    $this->storeId,
                    '1'
                ],
                [
                    'payment/' . WompiPa::CODE . '/environment_g/development/events_key',
                    ScopeInterface::SCOPE_STORE,
                    $this->storeId,
                    $expectedKey
                ]
            ]);

        $this->assertEquals($expectedKey, $this->config->getEventsKey($this->storeId));
    }

    public function testGetIntegrityKeyInProductionMode(): void
    {
        $expectedKey = 'prod_integrity_key_456';

        $this->scopeConfigMock->method('getValue')
            ->willReturnMap([
                [
                    'payment/' . WompiPa::CODE . '/' . WompiPa::ENVIRONMENT,
                    ScopeInterface::SCOPE_STORE,
                    $this->storeId,
                    '0'
                ],
                [
                    'payment/' . WompiPa::CODE . '/environment_g/production/integrity_key',
                    ScopeInterface::SCOPE_STORE,
                    $this->storeId,
                    $expectedKey
                ]
            ]);

        $this->assertEquals($expectedKey, $this->config->getIntegrityKey($this->storeId));
    }
}
