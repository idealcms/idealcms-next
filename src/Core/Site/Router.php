<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2019 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core\Site;

use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

class Router
{
    /**
     * @param \Zend\Diactoros\ServerRequest $request
     * @param \Zend\Diactoros\Response $response
     * @param \Relay\Runner $next
     * @return \Zend\Diactoros\Response
     */
    public function __invoke(ServerRequest $request, Response $response, $next): Response
    {
        // Write to the response body:
        $response->getBody()->write("site content\n");
        //$response->getBody()->write("<pre>" . print_r($request, true) . '</pre>');

        // optionally invoke the $next middleware and get back a new response
        $response = $next($request, $response);

        // NOT OPTIONAL: return the Response to the previous middleware
        return $response;
    }
}
