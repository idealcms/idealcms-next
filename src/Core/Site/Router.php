<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core\Site;

use Ideal\Core\Di;
use Ideal\Structure\Home\Site\Router as HomeRouter;
use Relay\Runner;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response;

class Router
{
    /**
     * @param ServerRequest $request
     * @param Response $response
     * @param Runner $next
     * @return Response
     * @throws \Exception
     */
    public function __invoke(ServerRequest $request, Response $response, Runner $next): Response
    {
        $di = Di::getInstance();

        // Определяем контроллер для запуска
        /** @var HomeRouter $homeRouter */
        $homeRouter = $di->create(HomeRouter::class, $request, $response);

        // Определяем нужный контроллер на основании запроса
        $controller = $homeRouter->getController();

        // Запускаем в работу контроллер структуры
        $response = $controller->run($response);

        /** @var Response $response */
        $response = $next($request, $response); // вызов следующего middleware в очереди

        return $response;
    }
}
