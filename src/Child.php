<?php

declare(strict_types=1);

namespace Antidot\React;

use RuntimeException;
use function pcntl_fork;
use function pcntl_waitpid;

class Child
{
    private const DEFAULT_NUMBER_OF_FORKS = 0;

    public static function fork(
        int $numberOfWorkers,
        callable $asyncServer,
        int $numberOfFork = self::DEFAULT_NUMBER_OF_FORKS
    ): int {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            throw new RuntimeException('The PHP pcntl extension is not available for Windows systems');
        }

        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new RuntimeException('Fork Failed');
        }

        if (0 === $pid) {
            $asyncServer();
            pcntl_waitpid($pid, $status);
            return $pid;
        }

        // @parent
        $numberOfWorkers--;
        ++$numberOfFork;
        if (self::DEFAULT_NUMBER_OF_FORKS < $numberOfWorkers) {
            self::fork($numberOfWorkers, $asyncServer, $numberOfFork);
        }

        pcntl_waitpid($pid, $status);
        return $pid;
    }
}
