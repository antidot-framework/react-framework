<?php

declare(strict_types=1);

namespace AntidotTest\React;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ChildTest extends TestCase
{
    public function testItShouldBeConstructedStatically(): void
    {
        $process = new Process([
            'php',
            '-r',
            'include "src/Child.php";\Antidot\React\Child::fork(1, function() { echo "test passed"; }, 0);'
        ]);
        $process->start();
        $process->wait();
        $this->assertSame('test passed', $process->getOutput());
    }
}
