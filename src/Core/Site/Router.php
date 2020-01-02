<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2019 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core\Site;

use Relay\Runner;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

class Router
{
    /**
     * @param ServerRequest $request
     * @param Response $response
     * @param Runner $next
     * @return Response
     */
    public function __invoke(ServerRequest $request, Response $response, $next): Response
    {
        // Write to the response body:
        $response->getBody()->write("site content\n");

        /** @var Response $response */
        $response = $next($request, $response); // вызов следующего middleware в очереди

        return $response;
    }
}
