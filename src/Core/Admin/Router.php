<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2019 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core\Admin;

use Ideal\Core\Config;
use Ideal\Core\Di;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;

class Router
{
    public function __invoke(ServerRequest $request, Response $response, $next): Response
    {
        $di = Di::getInstance();
        $config = $di->get(Config::class);

        // Получаем запрошенный url
        $uri = $request->getUri();
        $path = $uri->getPath();

        if (strpos($path, $config->cmsFolder) === 0) {
            // Если запрошена админка
            $response->getBody()->write("admin content\n");
        } else {
            // Если запрошена не админка, то продолжаем обработку очереди middleware
            $response = $next($request, $response);
        }

        return $response;
    }
}
