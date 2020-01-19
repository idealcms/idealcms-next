<?php
namespace Ideal\Core\Admin;

use Ideal\Core\Config;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Response;
use Relay\Relay;

class MiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $config = Config::getInstance();
        $config->adminFolder = '/strange';

        $response = new Response();
        $response->getBody()->write('not admin');

        $handler = $this->createMock(Relay::class);
        $handler->method('handle')
             ->willReturn($response);

        $request = (new \Laminas\Diactoros\ServerRequest(['REQUEST_URI' => '/admin']))
            ->withUri(new \Laminas\Diactoros\Uri('http://example.com/admin'))
            ->withMethod('GET');

        $middleware = new Middleware();
        $response = $middleware->process($request, $handler);

        $this->assertEquals('not admin', $response->getBody());

        $config->adminFolder = '/admin';

        $response = $middleware->process($request, $handler);
        $this->assertNotEquals('not admin', $response->getBody());
    }
}
