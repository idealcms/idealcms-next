<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core\Site;

use Exception;
use Ideal\Core\Di;
use Ideal\Structure\Home\Site\Router as HomeRouter;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Router implements MiddlewareInterface
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

        $di = Di::getInstance();

        // Определяем контроллер для запуска
        /** @var HomeRouter $homeRouter */
        $homeRouter = $di->create(HomeRouter::class, $request, $response);

        // Определяем нужный контроллер на основании запроса
        $controller = $homeRouter->getController();

        // Запускаем в работу контроллер структуры
        $response = $controller->run($response);

        return $response;
    }
}
