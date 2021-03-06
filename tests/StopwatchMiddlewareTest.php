<?php

namespace Leadz\GuzzleHttp\Stopwatch\Test;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

use Leadz\GuzzleHttp\Stopwatch\StopwatchMiddleware;

use \Mockery as m;

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

use PHPUnit\Framework\TestCase;

class StopwatchMiddlewareTest extends TestCase
{
    /**
     * @return array Durations.
     */
    public function expectProvider()
    {
        return [
            [42]
        ];
    }

    /**
     * @dataProvider expectProvider
     *
     * @param int $duration The expected duration.
     */
    public function testAddsXDurationHeader($duration)
    {
        // HandlerStack
        $response = new Response(200);
        $stack = new HandlerStack(new MockHandler([$response]));

        // Stopwatch
        $event = m::mock(StopwatchEvent::class);
        $event->shouldReceive('getDuration')->once()->andReturn($duration);
        $stopwatch = m::mock(Stopwatch::class);
        $stopwatch->shouldReceive('start')->once()->with('http://example.com');
        $stopwatch->shouldReceive('isStarted')->andReturn(true);
        $stopwatch->shouldReceive('stop')->once()->with('http://example.com')->andReturn($event);

        // Middleware
        $headerName = 'X-Duration';
        $middleware = new StopwatchMiddleware($stopwatch);
        $middleware->setHeaderName($headerName);
        $stack->push($middleware);

        $handler = $stack->resolve();

        // Request
        $request = new Request('GET', 'http://example.com');
        $promise = $handler($request, []);
        $response = $promise->wait();

        $this->assertEquals($response->getHeaderLine($headerName), $duration);
    }

    /**
     * @dataProvider expectProvider
     *
     * @param int $duration The expected duration.
     */
    public function testNullStopwatch($duration)
    {
        // HandlerStack
        $response = new Response(200);
        $stack = new HandlerStack(new MockHandler([$response]));

        // Middleware
        $middleware = new StopwatchMiddleware();
        $stack->push($middleware);

        $handler = $stack->resolve();

        // Request
        $request = new Request('GET', 'http://example.com');
        $promise = $handler($request, []);
        $response = $promise->wait();

        $this->assertNotEquals($response->getHeaderLine('X-Duration'), $duration);
    }

    /**
     * @dataProvider expectProvider
     */
    public function testStopwatchNotStarted($duration)
    {
        $stopwatch = m::mock(Stopwatch::class);
        $stopwatch->shouldReceive('start')->never();
        $stopwatch->shouldReceive('isStarted')->once()->andReturn(false);
        $stopwatch->shouldReceive('stop')->never();

        // HandlerStack
        $response = new Response(200);
        $stack = new HandlerStack(new MockHandler([$response]));

        // Middleware
        $headerName = 'X-Duration';
        $middleware = new StopwatchMiddleware($stopwatch);
        $middleware->setHeaderName($headerName);

        $stack->push($middleware);

        $handler = $stack->resolve();

        // Request
        $request = new Request('GET', 'http://example.com');
        $promise = $handler($request, []);
        $response = $promise->wait();
        $this->assertInstanceOf(Response::class, $response);
    }
}
