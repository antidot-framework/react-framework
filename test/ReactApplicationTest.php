<?php

declare(strict_types=1);

namespace AntidotTest\React;

use Antidot\Application\Http\Response\ErrorResponseGenerator;
use Antidot\Application\Http\RouteFactory;
use Antidot\Application\Http\Router;
use Antidot\Container\MiddlewareFactory;
use Antidot\React\MiddlewarePipeline;
use Antidot\React\ReactApplication;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use React\EventLoop\Factory;
use function Clue\React\Block\await;

class ReactApplicationTest extends TestCase
{
    private MiddlewarePipeline $pipeline;
    private Router $router;
    private MiddlewareFactory $middlewareFactory;
    private RouteFactory $routeFactory;
    private ErrorResponseGenerator $errorResponseGenerator;

    protected function setUp(): void
    {
        $this->pipeline = $this->createMock(MiddlewarePipeline::class);
        $this->router = $this->createMock(Router::class);
        $this->middlewareFactory = $this->createMock(MiddlewareFactory::class);
        $this->routeFactory = $this->createMock(RouteFactory::class);
        $this->errorResponseGenerator = $this->createMock(ErrorResponseGenerator::class);
    }

    /** @dataProvider getRoutes */
    public function testItShouldAddRoutesInSomeApplicationHttpMethod(string $method, array $params): void
    {
        $this->routeFactory->expects($this->once())
            ->method('create')
            ->with([strtoupper($method)], $params[1], $params[0], $params[2]);
        $this->router->expects($this->once())
            ->method('append');

        $application = new ReactApplication(
            $this->pipeline,
            $this->router,
            $this->middlewareFactory,
            $this->routeFactory,
            $this->errorResponseGenerator
        );

        $application->{$method}(...$params);
    }

    public function testItShouldAddMiddlewareInApplication(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->middlewareFactory->expects($this->once())
            ->method('create')
            ->with('SomeMiddleware')
            ->willReturn($middleware);
        $this->pipeline->expects($this->once())
            ->method('pipe')
            ->with($middleware);

        $application = new ReactApplication(
            $this->pipeline,
            $this->router,
            $this->middlewareFactory,
            $this->routeFactory,
            $this->errorResponseGenerator
        );

        $application->pipe('SomeMiddleware');
    }

    public function testItShouldHandleProcessThenReturnPromiseResponse(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('withAttribute')
            ->willReturn($request);
        $this->pipeline->expects($this->once())
            ->method('process');

        $application = new ReactApplication(
            $this->pipeline,
            $this->router,
            $this->middlewareFactory,
            $this->routeFactory,
            $this->errorResponseGenerator
        );

        await($application->process($request, $handler), Factory::create());
    }

    public function testItShouldProcessFailingRequestThenReturnAnErrorPromiseResponse(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('withAttribute')
            ->willReturn($request);
        $this->pipeline->expects($this->once())
            ->method('process')
            ->willThrowException(new Exception('fail'));

        $application = new ReactApplication(
            $this->pipeline,
            $this->router,
            $this->middlewareFactory,
            $this->routeFactory,
            $this->errorResponseGenerator
        );

        await($application->process($request, $handler), Factory::create());
    }

    public function testItShouldHandleRequestThenReturnPromiseResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('withAttribute')
            ->willReturn($request);
        $this->pipeline->expects($this->once())
            ->method('handle');

        $application = new ReactApplication(
            $this->pipeline,
            $this->router,
            $this->middlewareFactory,
            $this->routeFactory,
            $this->errorResponseGenerator
        );

        await($application->handle($request), Factory::create());
    }

    public function testItShouldHandleFailingRequestThenReturnAnErrorPromiseResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('withAttribute')
            ->willReturn($request);
        $this->pipeline->expects($this->once())
            ->method('handle')
            ->willThrowException(new Exception('fail'));

        $application = new ReactApplication(
            $this->pipeline,
            $this->router,
            $this->middlewareFactory,
            $this->routeFactory,
            $this->errorResponseGenerator
        );

        await($application->handle($request), Factory::create());
    }

    public function testItSouldFailWhenTryToRun(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You can\'t run application out of React PHP server.');

        $application = new ReactApplication(
            $this->pipeline,
            $this->router,
            $this->middlewareFactory,
            $this->routeFactory,
            $this->errorResponseGenerator
        );

        $application->run();
    }

    public function getRoutes(): array
    {
        return [
            ['get', ['/', [], 'home']],
            ['post', ['/', [], 'home']],
            ['patch', ['/', [], 'home']],
            ['put', ['/', [], 'home']],
            ['delete', ['/', [], 'home']],
            ['options', ['/', [], 'home']],
        ];
    }
}
