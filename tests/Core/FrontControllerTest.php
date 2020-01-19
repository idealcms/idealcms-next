<?php
namespace Ideal\Test;

use Ideal\Core\Config;
use Ideal\Core\FrontController;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FrontControllerTest extends TestCase
{
    public function testRun(): void
    {
        ob_start();
        $fc = new TestFrontController(dirname(__DIR__));
        $fc->run();
        $print = ob_get_clean();

        $this->assertStringContainsString('class1 start ', $print);
        $this->assertStringContainsString('class1 end ', $print);
        $this->assertStringContainsString('class2 start ', $print);
        $this->assertStringContainsString('class2 end ', $print);
    }
}

class TestFrontController extends FrontController
{
    protected function getRequest(): \Laminas\Diactoros\ServerRequest
    {
        $request = (new \Laminas\Diactoros\ServerRequest(['REQUEST_URI' => '/']))
            ->withUri(new \Laminas\Diactoros\Uri('http://example.com/'))
            ->withMethod('GET');

        return $request;
    }

    protected function load($root): Config
    {
        $config = Config::getInstance();
        $config->middleware = ['\\Ideal\\Test\\Class1', '\\Ideal\\Test\\Class2'];
        return $config;
    }
}

class Class1 implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        print 'class1 start ';
        $response = $handler->handle($request);
        print 'class1 end ';
        return $response;
    }
}

class Class2 implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        print 'class2 start ';
        $response = $handler->handle($request);
        print 'class2 end ';
        return $response;
    }
}
