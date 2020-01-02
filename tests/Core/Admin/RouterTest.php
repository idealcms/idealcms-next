<?php
namespace Ideal\Core\Admin;

use Ideal\Core\Config;
use Ideal\Core\Di;
use PHPUnit\Framework\TestCase;
use Relay\Runner;
use Laminas\Diactoros\Response;

class RouterTest extends TestCase
{
    public function testInvoke()
    {
        $di = Di::getInstance();
        $config = $di->get(Config::class);
        $config->cmsFolder = '/strange';

        $router = new Router();
        $response = new Response();
        $testResponse = new Response();
        $testResponse->getBody()->write("not admin");

        $next = $this->createMock(Runner::class);
        $next->method('__invoke')
             ->willReturn($testResponse);

        $request = (new \Laminas\Diactoros\ServerRequest())
            ->withUri(new \Laminas\Diactoros\Uri('http://example.com/admin'))
            ->withMethod('GET');

        $result = $router($request, $response, $next);

        $this->assertEquals('not admin', $result->getBody());

        $config->cmsFolder = '/admin';

        $result = $router($request, $response, $next);
        $this->assertNotEquals('not admin', $result->getBody());
    }
}
