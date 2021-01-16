<?php

declare(strict_types=1);

namespace AntidotTest\React;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Antidot\React\MiddlewarePipeline;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use React\EventLoop\Factory;
use RingCentral\Psr7\ServerRequest;
use function Clue\React\Block\await;
use function React\Promise\resolve;

class MiddlewarePipelineTest extends TestCase
{
    private const REQUEST_ID = 'd32f3aac-700b-4ec3-9081-c85e1d2f177f';
    private ServerRequestInterface $request;
    private MiddlewareInterface $middleware;

    protected function setUp(): void
    {
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->middleware = $this->createMock(MiddlewareInterface::class);
    }

    public function testItShouldHandleRequestThenReturnPromiseResponse(): void
    {
        $this->request->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturn(self::REQUEST_ID);
        $this->middleware->expects($this->once())
            ->method('process')
            ->with(
                $this->isInstanceOf(ServerRequestInterface::class),
                $this->isInstanceOf(RequestHandlerInterface::class)
            );
        $pipeline = new MiddlewarePipeline();
        $pipeline->pipe($this->middleware);
        $promiseResponse = $pipeline->handle($this->request)->then(
            function ($response) {
                $this->assertInstanceOf(ResponseInterface::class, $response);
                return $response;
            },
            function ($error) {
                $this->assertTrue(false);
                return new Exception($error);
            }
        );

        await(resolve($promiseResponse), Factory::create());
    }

    public function testItShouldRejectResponseWhenRequestHandlingFailed(): void
    {
        $this->request->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturn(self::REQUEST_ID);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error: test success.');
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->once())
            ->method('process')
            ->with(
                $this->isInstanceOf(ServerRequestInterface::class),
                $this->isInstanceOf(RequestHandlerInterface::class)
            )
            ->willThrowException(new Exception('Error: test success.'));
        $pipeline = new MiddlewarePipeline();
        $pipeline->pipe($middleware);
        $promiseResponse = $pipeline->handle($this->request);

        await(resolve($promiseResponse), Factory::create());
    }

    public function testItShouldProcessRequestThenReturnPromiseResponse(): void
    {
        $this->request->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturn(self::REQUEST_ID);
        $this->middleware->expects($this->once())
            ->method('process')
            ->with(
                $this->isInstanceOf(ServerRequestInterface::class),
                $this->isInstanceOf(RequestHandlerInterface::class)
            );

        $pipeline = new MiddlewarePipeline();

        $pipeline->pipe($this->middleware);
        $promiseResponse = $pipeline->process(
            $this->request,
            $this->createMock(RequestHandlerInterface::class)
        )->then(
            function ($response) {
                $this->assertInstanceOf(ResponseInterface::class, $response);
                return $response;
            },
            function ($error) {
                $this->assertTrue(false, $error->getMessage());
                return new Exception($error);
            }
        );

        await(resolve($promiseResponse), Factory::create());
    }

    public function testItShouldRejectResponseWhenRequestProcessingFailed(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error: test success.');
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->once())
            ->method('process')
            ->with(
                $this->isInstanceOf(ServerRequestInterface::class),
                $this->isInstanceOf(RequestHandlerInterface::class)
            )
            ->willThrowException(new Exception('Error: test success.'));
        $pipeline = new MiddlewarePipeline();
        $pipeline->pipe($middleware);
        $promiseResponse = $pipeline->process(
            new ServerRequest('GET', '/'),
            $this->createMock(RequestHandlerInterface::class)
        );

        await(resolve($promiseResponse), Factory::create());
    }
}
