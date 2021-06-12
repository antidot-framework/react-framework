<?php

declare(strict_types=1);

namespace Antidot\React;

use Generator;
use Psr\Http\Message\ResponseInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Throwable;

final class ResolveGenerator
{
    /**
     * @param callable<Generator>|ResponseInterface|PromiseInterface $generator
     */
    public static function toResponse(
        callable|ResponseInterface|PromiseInterface $callback
    ): PromiseInterface|ResponseInterface {
        if (false === is_callable($callback)) {
            return $callback;
        }

        return new Promise(
            static function (callable $resolve, callable $reject) use ($callback): void {
                try {
                    /** @var Generator<PromiseInterface|ResponseInterface> $generator */
                    $generator = $callback();
                    while ($generator->valid()) {
                        $item = $generator->current();
                        if ($item instanceof PromiseInterface) {
                            $item->then(static fn($solved) => $generator->send($solved));
                            continue;
                        }
                        $generator->send($item);
                    }
                    $resolve($generator->getReturn());
                } catch (Throwable $exception) {
                    $reject($exception);
                }
            }
        );
    }
}
