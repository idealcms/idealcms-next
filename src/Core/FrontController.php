<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2019 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core;

use Exception;
use Relay\Runner;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;

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
     * @throws Exception
     */
    public function run(string $webRoot): void
    {
        // Определяем корневую папку всей системы
        $root = stream_resolve_include_path($webRoot . '/../');

        // Определяем путь к файлу с переопределением зависимостей
        $definitions = stream_resolve_include_path($root . '/app/config/di.php');

        // todo разделение окружений на developer и production и включение кэша для production
        $cache = '';

        // Инициализируем контейнер и конфигуратор
        $di = Di::getInstance($definitions, $cache);

        // Получаем объект конфигурации
        /** @var Config $config */
        $config = $di->get(Config::class);
        // Загружаем список структур из конфигурационных файлов структур
        $config->load($root);

        // Инициализируем начальные $request, $response
        $request = $this->getRequest();
        $response = new Response();

        // Запускаем обработку очереди middleware
        $resolver = static function ($class) {
            return new $class();
        };

        $runner = new Runner($config->middleware, $resolver);
        $response = $runner($request, $response);

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
