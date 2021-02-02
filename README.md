# Antidot React Framework

[![link-packagist](https://img.shields.io/packagist/v/antidot-fw/react-framework.svg?style=flat-square)](https://packagist.org/packages/antidot-fw/react-framework)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/antidot-framework/react-framework/badges/quality-score.png?b=0.0.x)](https://scrutinizer-ci.com/g/antidot-framework/react-framework/?branch=0.0.x)
[![type-coverage](https://shepherd.dev/github/antidot-framework/react-framework/coverage.svg)](https://shepherd.dev/github/antidot-framework/react-framework)
[![Code Coverage](https://scrutinizer-ci.com/g/antidot-framework/react-framework/badges/coverage.png?b=0.0.x)](https://scrutinizer-ci.com/g/antidot-framework/react-framework/?branch=0.0.x)
[![Build Status](https://scrutinizer-ci.com/g/antidot-framework/react-framework/badges/build.png?b=0.0.x)](https://scrutinizer-ci.com/g/antidot-framework/react-framework/build-status/0.0.x)

## Requirements

* PHP ^7.4|^8.0
* [Antidot Framework](https://antidotfw.io)
* [DriftPHP Server](https://github.com/driftphp/server)  
* [React Http](https://github.com/reactphp/http)
* [React Promises](https://github.com/reactphp/promise)
* [Ramsey Uuid](https://github.com/ramsey/uuid)

## Description

This package allows running asynchronous PHP following PSR-15 middleware standard approach.

## Install

The preferred way to install this library is using the `reactive-antidot-starter` project.

```bash
composer create-project antidot-fw/reactive-antidot-starter
```

> [Antidot Framework Reactive Starter](https://github.com/antidot-framework/reactive-antidot-starter)

To install it on a existing Antidot Framework Project installation we need to tweak some configurations and replace or create new `index.php` file.

```bash
composer require antidot-fw/react-framework
```

## Config

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

Default Config:

```php
<?php

$config = [
    'server' => [
        'host' => '0.0.0.0',
        'port' => 5555,
        'buffer_size' => 4096,
        'max_concurrency' => 100,
        'workers' => 1,
        'static_folder' => 'public'
    ]
]

```

## Usage

It allows executing promises inside PSR-15 and PSR-7 Middlewares and request handlers

### PSR-15 Middleware

```php
<?php
declare(strict_types = 1);

namespace App;

use Antidot\React\PromiseResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SomeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new PromiseResponse(
            resolve($request)
                ->then(static fn(ServerrequestInsterface $request) => $handler->handle($request))
        );
    }
}
```

### PSR-7 Request Handler

```php
<?php
declare(strict_types = 1);

namespace App;

use Antidot\React\PromiseResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SomeMiddleware implements RequestHandlerInterface
{
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        return resolve($request)->then(
            function(ServerrequestInterface $request): ResponseInterface {
                return new Response('Hello World!!!');
            }
        );;
    }
}
```

## Server

Two new commands will be added to the Antidot Framework CLI tool, to allow running the application on top of [Drift server](https://driftphp.io/#/?id=the-server)

* `server:run`: Run Drift HTTP Server
* `server:watch`: Watch Drift HTTP Server for development purposes

```bash
$ bin/console
...
 server
  server:run               Run Drift HTTP Server
  server:watch             Watch Drift HTTP Server for development purposes
```

```bash
$ bin/console server:run -h
Description:
  Run Drift HTTP Server

Usage:
  server:run [options] [--] [<path>]

Arguments:
  path                                             The server will start listening to this address [default: "0.0.0.0:5555"]

Options:
      --static-folder[=STATIC-FOLDER]              Static folder path [default: "public"]
      --no-static-folder                           Disable static folder
      --debug                                      Enable debug
      --no-header                                  Disable the header
      --no-cookies                                 Disable cookies
      --no-file-uploads                            Disable file uploads
      --concurrent-requests[=CONCURRENT-REQUESTS]  Limit of concurrent requests [default: 100]
      --request-body-buffer[=REQUEST-BODY-BUFFER]  Limit of the buffer used for the Request body. In KiB. [default: 4096]
      --adapter[=ADAPTER]                          Server Adapter [default: "Antidot\React\DriftKernelAdapter"]
      --allowed-loop-stops[=ALLOWED-LOOP-STOPS]    Number of allowed loop stops [default: 0]
      --workers[=WORKERS]                          Number of workers. Use -1 to get as many workers as physical thread available for your system. Maximum of 128 workers. Option disabled for watch command. [default: 16]
  -q, --quiet                                      Do not output any message

```

```bash
$ bin/console server:watch -h
Description:
  Watch Drift HTTP Server for development purposes

Usage:
  server:watch [options] [--] [<path>]

Arguments:
  path                                             The server will start listening to this address [default: "0.0.0.0:5555"]

Options:
      --static-folder[=STATIC-FOLDER]              Static folder path [default: "public"]
      --no-static-folder                           Disable static folder
      --debug                                      Enable debug
      --no-header                                  Disable the header
      --no-cookies                                 Disable cookies
      --no-file-uploads                            Disable file uploads
      --concurrent-requests[=CONCURRENT-REQUESTS]  Limit of concurrent requests [default: 512]
      --request-body-buffer[=REQUEST-BODY-BUFFER]  Limit of the buffer used for the Request body. In KiB. [default: 2048]
      --adapter[=ADAPTER]                          Server Adapter [default: "drift"]
      --allowed-loop-stops[=ALLOWED-LOOP-STOPS]    Number of allowed loop stops [default: 0]
      --workers[=WORKERS]                          Number of workers. Use -1 to get as many workers as physical thread available for your system. Maximum of 128 workers. Option disabled for watch command. [default: 1]
  -q, --quiet                                      Do not output any message

```

