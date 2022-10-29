# Guzzle middleware to time requests

[![Author](http://img.shields.io/badge/author-@bmancone-blue.svg?style=flat-square)](https://github.com/bmancone)
[![Latest Version](https://img.shields.io/packagist/v/bmancone/guzzle-stopwatch-middleware.svg?style=flat-square)](https://packagist.org/packages/bmancone/guzzle-stopwatch-middleware)
[![Build Status](https://img.shields.io/travis/bmancone/guzzle-stopwatch-middleware.svg?style=flat-square&branch=master)](https://travis-ci.org/bmancone/guzzle-stopwatch-middleware)

## Installation

```bash
composer require bmancone/guzzle-stopwatch-middleware
```

## Usage

Requires an instance of `Symfony\Component\Stopwatch\Stopwatch`.

```php
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;

use Symfony\Component\Stopwatch\Stopwatch;

Use Leadz\GuzzleHttp\Stopwatch\StopwatchMiddleware;

// Create the default HandlerStack
$stack = HandlerStack::create();

// Create the middleware
$middleware = new StopwatchMiddleware(new Stopwatch());

// Push the Middleware to the stack
$stack->push($middleware);

// Create the client
$client = new Client([
    'handler' => $stack
]);

// Send the request
$request = new Request('GET', 'https://en.wikipedia.org/wiki/Main_Page');
$response = $client->send($request);

// Get the duration of the request
printf('Request to [%s] took [%dms]', (string)$request->getUri(), $response->getHeaderLine('X-Duration'));
```

## Symfony Profiler

If you are using Symfony, simply inject `debug.stopwatch` (or use autowiring), this will add events to the profiler timeline.
  
  
