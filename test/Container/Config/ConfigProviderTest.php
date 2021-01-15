<?php

namespace AntidotTest\Tactician\Container\Config;

use Antidot\Tactician\Container\Config\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testItShouldReturnTheConfigArray(): void
    {
        $configProvider = new ConfigProvider();
        $this->assertIsArray($configProvider());
    }
}
