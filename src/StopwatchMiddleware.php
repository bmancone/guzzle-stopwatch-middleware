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
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * Creates a callable Middleware for timing guzzle requests.
     *
     * @param Stopwatch $stopwatch
     */
    public function __construct(Stopwatch $stopwatch = null)
    {
        $this->stopwatch = $stopwatch;
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

            if (null !== $this->stopwatch) {
                $event = $this->stopwatch->stop((string)$request->getUri());
                return $response->withHeader('X-Duration', $event->getDuration());
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
