<?php
declare(strict_types=1);

namespace Saulmoralespa\WompiPa\Tests\Unit\Model\Config\Source;

use PHPUnit\Framework\TestCase;
use Saulmoralespa\WompiPa\Model\Config\Source\Environment;

class EnvironmentTest extends TestCase
{
    /**
     * @var Environment
     *
     * Mock object for the Environment class.
     */
    private Environment $environmentMock;

    protected function setUp(): void
    {
        $this->environmentMock = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
    }

    public function testToOptionArray()
    {
        $result = $this->environmentMock->toOptionArray();

        // Compare values
        $this->assertCount(2, $result);
        $this->assertEquals('1', $result[0]['value']);
        $this->assertEquals('0', $result[1]['value']);

        // Compare label text (Phrase objects have different instances)
        $this->assertEquals('Development', (string)$result[0]['label']);
        $this->assertEquals('Production', (string)$result[1]['label']);
    }
}
