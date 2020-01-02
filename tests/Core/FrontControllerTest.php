<?php
namespace Ideal\Test;

use Ideal\Core\Di;
use Ideal\Core\FrontController;
use PHPUnit\Framework\TestCase;

class FrontControllerTest extends TestCase
{
    public function testRun()
    {
        $di = Di::getInstance();
        $di->set('Ideal\Core\Config', \DI\create('Ideal\Test\TestConfig'));

        ob_start();
        $fc = new TestFrontController();
        $fc->run('');
        $print = ob_get_contents();
        ob_end_clean();

        $this->assertContains('class1 start ', $print);
        $this->assertContains('class1 end ', $print);
        $this->assertContains('class2 start ', $print);
        $this->assertContains('class2 end ', $print);
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

    public function load(string $root)
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
