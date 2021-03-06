<?php

declare (strict_types = 1);

namespace Ypf;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ypf\Application\Cgi as DefaultFactory;
use Ypf\Log\VoidLogger;

class Application implements LoggerAwareInterface
{
    const VERSION = '3.1.0';

    protected $container;

    protected static $instances = null;
    use LoggerAwareTrait;

    public function __construct(array $services)
    {
        $container = new Container($services);

        $this->container = $container;
        $logger = $container->has('logger') ?
        $container->get('logger') : new VoidLogger();
        $this->setLogger($logger);

        static::$instances = &$this;
    }

    public static function getInstance()
    {
        return static::$instances;
    }

    public static function getContainer()
    {
        return static::$instances->container;
    }

    public function handleRequest(ServerRequestInterface $request)
    {
        try {
            $middleware = $this->container->has('middleware') ?
            $this->container->get('middleware') : [];
            $dispatcher = new Dispatcher($middleware);

            return $dispatcher->dispatch($request);
        } catch (\Throwable $ex) {
            $this->logger->critical($ex->getMessage(), [
                'trace' => $ex->getTraceAsString(),
            ]);

            $headers = [
                'X-Powered-By' => 'YPF/' . static::VERSION,
                'content-type' => 'text/plain; charset=utf-8',
            ];
            $payload = getenv('DEBUG') ? $ex->__toString() : 'Unexpected Server Error';

            return new Response(500, $headers, $payload);
        }
    }

    public function run()
    {
        $server = $this->container->has('factory') ? $this->container->get('factory') :
        new DefaultFactory();
        $server->build()->run();
    }
}
