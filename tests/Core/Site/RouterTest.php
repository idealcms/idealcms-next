<?php
namespace Ideal\Core\Site;

use PHPUnit\Framework\TestCase;
use Relay\Runner;
use Zend\Diactoros\Response;

class RouterTest extends TestCase
{

    public function testInvoke()
    {
        $router = new Router();
        $response = new Response();
        $testResponse = new Response();
        $testResponse->getBody()->write("next response");

        $next = $this->createMock(Runner::class);
        $next->method('__invoke')
             ->willReturn($testResponse);

        $request = (new \Zend\Diactoros\ServerRequest())
            ->withUri(new \Zend\Diactoros\Uri('http://example.com/test'))
            ->withMethod('GET');

        $result = $router($request, $response, $next);

        $this->assertEquals('next response', $result->getBody());
    }
}
