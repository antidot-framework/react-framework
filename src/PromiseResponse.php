<?php

declare(strict_types=1);

namespace Antidot\React;

use Generator;
use React\Promise\PromiseInterface;
use RingCentral\Psr7\Response;
use function React\Promise\resolve;

final class PromiseResponse extends Response implements PromiseInterface
{
    private PromiseInterface $promise;
    protected $stream;

    /**
     * @param PromiseInterface $promise
     * @param mixed $body
     * @param int $status
     * @param array $headers
     */
    public function __construct(
        PromiseInterface $promise,
        $body = null,
        int $status = 200,
        array $headers = []
    ) {
        parent::__construct($status, $headers, $body);
        $this->promise = $promise;
    }

    /**
     * @param callable<Generator> $callback
     * @return static
     */
    public static function fromGeneratorCallback(callable $callback): self
    {
        return new self(resolve(function () use ($callback): Generator {
            /** @var Generator $generator */
            $generator = $callback();

            return $generator;
        }));
    }

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onProgress
     */
    final public function then(
        callable $onFulfilled = null,
        callable $onRejected = null,
        callable $onProgress = null
    ): PromiseInterface {
        return $this->promise->then($onFulfilled, $onRejected, $onProgress);
    }
}
