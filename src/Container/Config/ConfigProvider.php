<?php

namespace Antidot\React\Container\Config;

use Antidot\Application\Http\Application;
use Antidot\React\ReactApplicationFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'factories' => [
                    Application::class => ReactApplicationFactory::class,
                ],
            ]
        ];
    }
}
