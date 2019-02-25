<?php

namespace Leadz\GuzzleHttp\Stopwatch;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Stopwatch Middleware for Guzzle.
 *
 * @author Brice Mancone <brice.mancone@gmail.com>
 */
class StopwatchMiddleware
{
    /**
     * @var Stopwatch The stopwatch service.
     */
    protected $stopwatch;

    /**
     * @var string The name of the header where the request duration is stored.
     */
    protected $headerName = 'X-Duration';

    /**
     * Creates a callable Middleware for timing guzzle requests.
     *
     * @param Stopwatch $stopwatch The stopwatch service.
     */
    public function __construct(Stopwatch $stopwatch = null)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * Sets the name of the header where the request duration is stored.
     *
     * @param string $headerName The name of the header where the request duration is stored.
     *
     * @return StopwatchMiddleware
     */
    public function setHeaderName($headerName)
    {
        $this->headerName = $headerName;

        return $this;
    }

    /**
     * Starts the stopwatch for the given request.
     *
     * @param RequestInterface $request The request.
     */
    protected function onBefore(RequestInterface $request)
    {
        $this->stopwatch && $this->stopwatch->start((string)$request->getUri());
    }

    /**
     * Returns a callable handler that stops the stopwatch for the given request when it is successful.
     * (Fills the X-Duration header with the duration of the request in milliseconds.)
     *
     * @param RequestInterface $request The request.
     *
     * @return \Closure Handler.
     */
    protected function onSuccess(RequestInterface $request)
    {
        return function (ResponseInterface $response) use ($request) {

            if (null !== $this->stopwatch && $this->stopwatch->isStarted((string)$request->getUri())) {
                $event = $this->stopwatch->stop((string)$request->getUri());

                return $response->withHeader($this->headerName, $event->getDuration());
            }

            return $response;
        };
    }

    /**
     * Called when the middleware is handled.
     *
     * @param callable $handler
     *
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function ($request, array $options) use ($handler) {

            $this->onBefore($request);

            return $handler($request, $options)->then($this->onSuccess($request));
        };
    }
}
