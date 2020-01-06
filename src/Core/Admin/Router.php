<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core\Admin;

use Exception;
use Ideal\Core\Di;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
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
        $di = Di::getInstance();
        $config = $di->get(Config::class);

        // Получаем запрошенный url
        $uri = $request->getUri();
        $path = $uri->getPath();

        if (strpos($path, $config->adminFolder) === 0) {
            // Если запрошена админка
            $response = new Response();
            $response->getBody()->write("admin content\n");
        } else {
            // Если запрошена не админка, то продолжаем обработку очереди middleware
            $response = $handler->handle($request);
        }

        return $response;
    }
}
