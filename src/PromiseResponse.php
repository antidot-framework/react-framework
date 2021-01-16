<?php

declare(strict_types=1);

namespace Antidot\React;

use Psr\Http\Message\ResponseInterface;
use React\Promise\PromiseInterface;
use RingCentral\Psr7\Response;
use Throwable;

class PromiseResponse extends Response implements PromiseInterface
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

    final public function then(
        callable $onFulfilled = null,
        callable $onRejected = null,
        callable $onProgress = null
    ): PromiseInterface {
        return $this->promise->then($onFulfilled, $onRejected, $onProgress);
    }
}
