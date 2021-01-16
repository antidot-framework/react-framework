# Antidot React Framework

[![link-packagist](https://img.shields.io/packagist/v/antidot-fw/react-framework.svg?style=flat-square)](https://packagist.org/packages/antidot-fw/react-framework)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/antidot-framework/react-framework/badges/quality-score.png?b=0.0.x)](https://scrutinizer-ci.com/g/antidot-framework/react-framework/?branch=0.0.x)
[![type-coverage](https://shepherd.dev/github/antidot-framework/react-framework/coverage.svg)](https://shepherd.dev/github/antidot-framework/react-framework)
[![Code Coverage](https://scrutinizer-ci.com/g/antidot-framework/react-framework/badges/coverage.png?b=0.0.x)](https://scrutinizer-ci.com/g/antidot-framework/react-framework/?branch=0.0.x)
[![Build Status](https://scrutinizer-ci.com/g/antidot-framework/react-framework/badges/build.png?b=0.0.x)](https://scrutinizer-ci.com/g/antidot-framework/react-framework/build-status/0.0.x)

## Requirements:

* PHP ^7.4|^8.0
* Antidot Framework
* React Http
* Ramsey Uuid

## Description

This package allows running both common synchronous and modern asynchronous PHP following PSR-15 middleware standard approach.

## Install

The preferred way to install this library is using the `reactive-antidot-starter` project.

```bash
composer create-project antidot-fw/reactive-antidot-starter
```

> [Reactive Antidot Framework](https://github.com/antidot-framework/reactive-antidot-starter)

To install it on a existing Antidot Framework Project installation we need to tweak some configurations and replace or create new `index.php` file.

```bash
composer require antidot-fw/react-framework
```

### Config

* Disable LaminasRequest Handler Runner
* Load Antidot React Config Provider after Antidot Framework provider

Example config from starter project
```php
<?php
// config/config.php

declare(strict_types=1);

use Antidot\DevTools\Container\Config\ConfigProvider as DevToolsConfigProvider;
use Antidot\SymfonyConfigTranslator\Container\Config\ConfigAggregator;
use Antidot\Yaml\YamlConfigProvider;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\PhpFileProvider;

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = [
    'config_cache_path' => 'var/cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
    \WShafer\PSR11MonoLog\ConfigProvider::class,
    \Antidot\Event\Container\Config\ConfigProvider::class,
    \Antidot\Logger\Container\Config\ConfigProvider::class,
    \Antidot\Cli\Container\Config\ConfigProvider::class,
    \Antidot\Fast\Router\Container\Config\ConfigProvider::class,
    \Antidot\Container\Config\ConfigProvider::class,
    \Antidot\React\Container\Config\ConfigProvider::class,
    class_exists(DevToolsConfigProvider::class) ? DevToolsConfigProvider::class : fn() => [],
    new PhpFileProvider(realpath(__DIR__).'/services/{{,*.}prod,{,*.}local,{,*.}dev}.php'),
    new YamlConfigProvider(realpath(__DIR__).'/services/{{,*.}prod,{,*.}local,{,*.}dev}.yaml'),
    new ArrayProvider($cacheConfig),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
```

The create your React Http server

```php
#!/usr/bin/env php
<?php
// public/index.php or everywhere you want to allocate your server

declare(strict_types=1);

use Antidot\Application\Http\Application;
use Antidot\React\PromiseResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use React\EventLoop\Factory;
use React\Http\Middleware\LimitConcurrentRequestsMiddleware;
use React\Http\Middleware\RequestBodyBufferMiddleware;
use React\Http\Middleware\RequestBodyParserMiddleware;
use React\Http\Middleware\StreamingRequestMiddleware;
use React\Http\Server;
use React\Socket\Server as Socket;
use function React\Promise\resolve;

require 'vendor/autoload.php';

call_user_func(static function () {
    $loop = Factory::create();
    $container = require 'config/container.php';
    $application = $container->get(Application::class);
    (require 'router/middleware.php')($application, $container);
    (require 'router/routes.php')($application, $container);

    $server = new Server(
        $loop,
        new StreamingRequestMiddleware(),
        new LimitConcurrentRequestsMiddleware(100), // 100 concurrent buffering handlers
        new RequestBodyBufferMiddleware(4 * 1024 * 1024), // 4 MiB
        new RequestBodyParserMiddleware(),
        static function (ServerRequestInterface $request) use ($application) {
            try {
                $response = new PromiseResponse(
                    resolve($request)
                        ->then(static fn ($request) => $request->withAttribute('request_id', Uuid::uuid4()->toString()))
                        ->then(static fn ($request) => $application->handle($request))
                );
            } catch (Throwable $exception) {
                if (!empty($e = $exception->getPrevious())) {
                    $exception = $e;
                }

                $response = new HtmlResponse(
                    sprintf(
                        '%s in file %s in line %s.',
                        $exception->getMessage(),
                        $exception->getFile(),
                        $exception->getLine()
                    )
                );
            }

            return resolve($response);
        }
    );

    $server->on('error', function ($err) {
        dump($err);
    });

    $socket = new Socket('0.0.0.0:8080', $loop);
    $server->listen($socket);

    $loop->run();
});

```
