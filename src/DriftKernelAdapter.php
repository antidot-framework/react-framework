<?php

declare(strict_types=1);

namespace Antidot\React;

use Antidot\Application\Http\Application;
use Drift\Console\OutputPrinter;
use Drift\Server\Adapter\KernelAdapter;
use Drift\Server\Context\ServerContext;
use Drift\Server\Mime\MimeTypeChecker;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Filesystem\FilesystemInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class DriftKernelAdapter implements KernelAdapter
{
    private ServerContext $serverContext;
    private MimeTypeChecker $mimeTypeChecker;
    private string $rootPath;
    private ContainerInterface $container;
    private ReactApplication $application;
    /** @var object|FilesystemInterface|null */
    private ?object $filesystem = null;

    /**
     * DriftKernelAdapter constructor.
     * @param ServerContext $serverContext
     * @param MimeTypeChecker $mimeTypeChecker
     * @param string $rootPath
     * @psalm-suppress UndefinedClass
     * @param FilesystemInterface|null $filesystem
     */
    public function __construct(
        ServerContext $serverContext,
        MimeTypeChecker $mimeTypeChecker,
        string $rootPath,
        ?FilesystemInterface $filesystem
    ) {
        $container = require $rootPath . '/config/container.php';
        assert($container instanceof ContainerInterface);
        $application = $container->get(Application::class);
        assert($application instanceof ReactApplication);
        (require $rootPath . '/router/middleware.php')($application, $container);
        (require $rootPath . '/router/routes.php')($application, $container);
        $this->container = $container;
        $this->application = $application;
        $this->serverContext = $serverContext;
        $this->filesystem = $filesystem;
        $this->mimeTypeChecker = $mimeTypeChecker;
        $this->rootPath = $rootPath;
    }

    /** @psalm-suppress MixedReturnTypeCoercion */
    public static function create(
        LoopInterface $loop,
        string $rootPath,
        ServerContext $serverContext,
        OutputPrinter $outputPrinter,
        MimeTypeChecker $mimeTypeChecker,
        ?FilesystemInterface $filesystem = null
    ): PromiseInterface {
        if ($filesystem && !class_exists(FilesystemInterface::class)) {
            throw new \RuntimeException('Install react/filesystem package.');
        }

        return resolve(new self($serverContext, $mimeTypeChecker, $rootPath, $filesystem))
            ->then(fn (KernelAdapter $adapter): KernelAdapter => $adapter);
    }

    /**
     * @psalm-suppress LessSpecificImplementedReturnType
     * @param ServerRequestInterface $request
     * @return PromiseResponse
     */
    public function handle(ServerRequestInterface $request): PromiseResponse
    {
        return $this->application->handle($request);
    }

    public static function getStaticFolder(): ?string
    {
        return 'public';
    }

    public function shutDown(): PromiseInterface
    {
        return resolve('nothing to do');
    }

    /**
     * Get watcher folders.
     *
     * @return string[]
     */
    public static function getObservableFolders(): array
    {
        return ['src', 'public', 'templates'];
    }

    /**
     * Get watcher folders.
     *
     * @return string[]
     */
    public static function getObservableExtensions(): array
    {
        return ['php', 'yml', 'yaml', 'xml', 'css', 'js', 'html', 'twig'];
    }

    /**
     * Get watcher ignoring folders.
     *
     * @return string[]
     */
    public static function getIgnorableFolders(): array
    {
        return ['var'];
    }
}
