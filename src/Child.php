<?php


namespace Antidot\React;

class Child
{
    public static function fork(int $numberOfWorkers, callable $asyncServer, int $numberOfFork = 0): void
    {
        $pid = pcntl_fork();
        if (-1 === $pid) {
            // @fail
            die('Fork failed');
        }

        if (0 === $pid) {
            $asyncServer();
            pcntl_waitpid($pid, $status);
            return;
        }

        // @parent
        $numberOfWorkers--;
        ++$numberOfFork;
        if ($numberOfWorkers > 0) {
            self::fork($numberOfWorkers, $asyncServer, $numberOfFork);
        }

        pcntl_waitpid($pid, $status);
    }
}
