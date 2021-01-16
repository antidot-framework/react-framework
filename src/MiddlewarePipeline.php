<?php

declare(strict_types=1);

namespace Antidot\React;

use Antidot\Application\Http\Handler\NextHandler;
use Antidot\Application\Http\Middleware\Pipeline;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use SplQueue;
use Throwable;
use function React\Promise\reject;
use function React\Promise\resolve;

class MiddlewarePipeline implements Pipeline
{
    /** @var array<SplQueue> */
    public array $concurrentPipelines;
    /** @var array<MiddlewareInterface> */
    private array $middlewareCollection;

    /**
     * @param array<MiddlewareInterface> $middlewareCollection
     * @param array<SplQueue> $concurrentPipelines
     */
    public function __construct(
        array $middlewareCollection = [],
        array $concurrentPipelines = []
    ) {
        $this->concurrentPipelines = $concurrentPipelines;
        $this->middlewareCollection = $middlewareCollection;
    }

    public function pipe(MiddlewareInterface $middleware): void
    {
        $this->middlewareCollection[] = $middleware;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var string $requestId */
        $requestId = $request->getAttribute('request_id');
        $this->setCurrentPipeline($requestId);

        return new PromiseResponse(resolve($request)->then(
            function (ServerRequestInterface $request) {
                /** @var string $requestId */
                $requestId = $request->getAttribute('request_id');
                try {
                    /** @var MiddlewareInterface $middleware */
                    $middleware = $this->concurrentPipelines[$requestId]->dequeue();

                    $response = $middleware->process($request, $this);
                    unset($this->concurrentPipelines[$requestId]);

                    return resolve($response);
                } catch (Throwable $exception) {
                    unset($this->concurrentPipelines[$requestId]);

                    return reject($exception);
                }
            }
        ));
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ?string $requestId */
        $requestId = $request->getAttribute('request_id');
        if (!$requestId) {
            $requestId = Uuid::uuid4()->toString();
            $request = $request->withAttribute('request_id', $requestId);
        }
        $this->setCurrentPipeline($requestId);

        return new PromiseResponse(resolve($request)
            ->then(function (ServerRequestInterface $request) use ($handler) {
                /** @var string $requestId */
                $requestId = $request->getAttribute('request_id');
                try {
                    /** @var SplQueue<MiddlewareInterface> $queue */
                    $queue = $this->concurrentPipelines[$requestId];
                    $next = new NextHandler($queue, $handler);

                    return resolve($next->handle($request));
                } catch (Throwable $exception) {
                    unset($this->concurrentPipelines[$requestId]);

                    return reject($exception);
                }
            }));
    }

    private function setCurrentPipeline(string $requestId): void
    {
        if (empty($this->concurrentPipelines[$requestId])) {
            $queue = new SplQueue();
            foreach ($this->middlewareCollection as $middlewareName) {
                $queue->enqueue($middlewareName);
            }
            $this->concurrentPipelines[$requestId] = $queue;
        }
    }
}
