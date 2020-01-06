<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core;

use Relay\Relay;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Relay\Runner;

/**
 * Front Controller реализует обработку HTTP-запросов в соответствии со стандартом PSR-7
 *
 */
class FrontController
{
    /**
     * Запуск FrontController'а
     *
     * Проводится роутинг, определяется контроллер страницы и отображаемый текст.
     * Выводятся HTTP-заголовки и отображается текст, сгенерированный с помощью view в controller
     * @param string $webRoot
     */
    public function run(string $webRoot): void
    {
        // Определяем корневую папку всей системы
        $root = stream_resolve_include_path($webRoot . '/../');

        // Получаем объект конфигурации
        $config = Config::getInstance();
        // Загружаем список структур из конфигурационных файлов структур
        $config->load($root);

        // Инициализируем начальные $request, $response
        $request = $this->getRequest();
        $response = new Response();

        // Запускаем обработку очереди middleware
        $resolver = static function ($class) {
            if ($class === false) {
                // Обработчик для конца очереди, возвращающий чистый Response
                return function (ServerRequest $request, Runner $runner) {
                    return new Response();
                };
            }
            return new $class();
        };

        $relay = new Relay($config->middleware, $resolver);
        $response = $relay->handle($request);

        // Выводим в браузер полученный ответ сервера
        http_response_code($response->getStatusCode()); // $response->getReasonPhrase()
        foreach ($response->getHeaders() as $header => $values) {
            printf("%s: %s\n", $header, implode(', ', $values));
        }
        echo $response->getBody();
    }

    /**
     * Метод для возможности переопределения объекта Request для тестов
     *
     * @return ServerRequest
     */
    protected function getRequest(): ServerRequest
    {
        return ServerRequestFactory::fromGlobals();
    }
}
