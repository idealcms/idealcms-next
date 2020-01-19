<?php
namespace Ideal\Core\Site;

use Ideal\Core\Config;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Response;
use Relay\Relay;

class MiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $config = Config::getInstance();
        $config->structures = [[
            'id' => 1,
            'structure' => 'Ideal_Part',
            'name' => 'Страницы',
            'isShow' => 1,
            'hasTable' => true,
            'startName' => 'Главная',
            'structures' => [],
            'url' => ''
        ]];
        $config->set(\Ideal\Structure\Home\Site\Router::class, testHomeRouter::class);
        $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);

        $response = new Response();
        $response->getBody()->write('not modified');

        $handler = $this->createMock(Relay::class);
        $handler->method('handle')
                ->willReturn($response);

        $request = (new \Laminas\Diactoros\ServerRequest(['REQUEST_URI' => '/']))
            ->withUri(new \Laminas\Diactoros\Uri('https://example.com/'))
            ->withMethod('GET');

        $middleware = new Middleware();
        $response = $middleware->process($request, $handler);

        $this->assertNotEquals('not modified', (string)$response->getBody());
        $this->assertStringContainsString('test content', (string)$response->getBody());
    }
}

class testHomeRouter extends \Ideal\Core\Site\Router
{
    public function route(array $path, array $url): Router
    {
        return $this;
    }

    public function getController(): \Ideal\Core\Site\Controller
    {
        return new testController($this->request, $this->response);
    }
}

class testController extends \Ideal\Core\Site\Controller
{
    public function run(): Response
    {
        // Write to the response body:
        $this->response->getBody()->write('test content');

        return $this->response;
    }
}
