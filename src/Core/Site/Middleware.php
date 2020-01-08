<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core\Site;

use Exception;
use Ideal\Core\Config;
use Ideal\Structure\Home\Site\Router as HomeRouter;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Middleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Вызов следующего middleware в очереди
        $response = $handler->handle($request);

        $config = Config::getInstance();

        // Определяем контроллер для запуска
        /** @var HomeRouter $homeRouter */
        $homeRouter = $config->create(HomeRouter::class, $request, $response);

        $path = [$config->getStartStructure()];
        $requestUri = $request->getServerParams()['REQUEST_URI'];
        $url = $requestUri === '/' ? [] : explode('/', $requestUri);

        // Определяем роутер отображаемой страницы
        $router = $homeRouter->route($path, $url);

        // Определяем нужный контроллер на основании запроса
        /** @var \Ideal\Core\Site\Controller $controller */
        $controller = $router->getController();

        // Запускаем в работу контроллер структуры
        $response = $controller->run();

        return $response;
    }
}
