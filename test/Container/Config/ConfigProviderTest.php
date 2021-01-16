<?php

namespace AntidotTest\Tactician\Container\Config;

use Antidot\Application\Http\Application;
use Antidot\React\Container\Config\ConfigProvider;
use Antidot\React\ReactApplicationFactory;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testItShouldReturnTheConfigArray(): void
    {
        $configProvider = new ConfigProvider();
        $this->assertIsArray($configProvider());
        $this->assertSame(
            ['dependencies' => ['factories' => [Application::class => ReactApplicationFactory::class]]],
            $configProvider(),
        );
    }
}
