<?php

declare(strict_types=1);

namespace AntidotTest\React;

use Antidot\React\PromiseResponse;
use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;
use Throwable;
use function Clue\React\Block\await;
use function React\Promise\reject;
use function React\Promise\resolve;

class PromiseResponseTest extends TestCase
{
    private const RESPONSE_BODY = 'Hello world!!';

    public function testItShouldContainAndResolveAPromise(): void
    {
        $promise = resolve(self::RESPONSE_BODY)->then(
            function ($response) {
                $this->assertSame(self::RESPONSE_BODY, $response);
                return new HtmlResponse($response);
            },
            function ($error): Throwable {
                $this->assertTrue(false);
                return new Exception($error);
            }
        );

        $promiseResponse = new PromiseResponse($promise, 'Waiting...');
        $this->assertSame(200, $promiseResponse->getStatusCode());
        $this->assertSame('Waiting...', $promiseResponse->getBody()->getContents());

        $response = await(resolve($promiseResponse), Factory::create());
        $this->assertSame(self::RESPONSE_BODY, $response->getBody()->getContents());
    }

    public function testItShouldContainAndRejectAPromise(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(self::RESPONSE_BODY);
        $promise = reject(new Exception(self::RESPONSE_BODY));

        $promiseResponse = new PromiseResponse($promise->then(
            function ($data) {
                throw $data;
            },
            function (Throwable $error): Throwable {
                $this->assertSame(self::RESPONSE_BODY, $error->getMessage());
                $this->assertInstanceOf(Exception::class, $error);
                throw $error;
            }
        ));

        await(resolve($promiseResponse), Factory::create());
    }
}
