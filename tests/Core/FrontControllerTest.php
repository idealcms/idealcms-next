<?php
namespace Ideal\Test;

use Ideal\Core\Di;
use Ideal\Core\FrontController;
use PHPUnit\Framework\TestCase;

class FrontControllerTest extends TestCase
{
    public function testRun(): void
    {
        $di = Di::getInstance();
        $di->set('Ideal\Core\Config', 'Ideal\Test\TestConfig');

        ob_start();
        $fc = new TestFrontController();
        $fc->run('');
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
        $request = (new \Laminas\Diactoros\ServerRequest())
            ->withUri(new \Laminas\Diactoros\Uri('http://example.com/'))
            ->withMethod('GET');

        return $request;
    }
}

class TestConfig extends \Ideal\Core\Config
{
    public function __get(string $name)
    {
        $value = parent::__get($name);
        if ($name === 'middleware') {
            $value = ['\Ideal\Test\Class1', '\Ideal\Test\Class2'];
        }
        return $value;
    }

    public function load(string $root): void
    {
    }

}

class Class1
{
    public function __invoke($request, $response, $next)
    {
        print 'class1 start ';
        $response = $next($request, $response);
        print 'class1 end ';
        return $response;
    }
}

class Class2
{
    public function __invoke($request, $response, $next)
    {
        print 'class2 start ';
        $response = $next($request, $response);
        print 'class2 end ';
        return $response;
    }
}
