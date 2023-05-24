<?php


namespace Tests\Unit\pincore\component\store\config;

use pinoox\component\store\config\Config;
use pinoox\component\store\config\strategy\ConfigStrategyInterface;
use Tests\Support\UnitTester;

class ConfigTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    protected function _before()
    {
    }

    // tests
    public function testSaveAndRetrieveKeyValue()
    {
        // Arrange
        $strategy = $this->createMock(ConfigStrategyInterface::class);
        $config = new Config($strategy);
        $key = 'development';
        $expectedValue = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'pinoox',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
        ];

        // Expect the ConfigStrategyInterface to save and retrieve the key-value pair
        $strategy->expects($this->once())
            ->method('set')
            ->with($key, $expectedValue);

        $strategy->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($expectedValue);

        // Act
        $config->set($key, $expectedValue);
        $savedValue = $config->get($key);

        // Assert
        $this->assertEquals($expectedValue, $savedValue);
    }
}
