<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
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
     * Проводит общую настройку среды выполнения
     *
     * @param string $webRoot Путь к публичной папке
     */
    public function __construct(string $webRoot)
    {
        error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING); //| E_STRICT
        setlocale(LC_ALL, 'ru_RU.UTF8');
        mb_internal_encoding('UTF-8'); // наша кодировка всегда UTF-8

        if (get_magic_quotes_gpc()) {
            die('Включены magic_quotes! Отключите их в настройках хостинга, иначе система работать не будет.');
        }

        // Устанавливаем часовой пояс
        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set('Europe/Moscow');
        }

        // Устанавливаем обработчик обычных ошибок скриптов
        set_error_handler('\Ideal\Core\Error::errorHandler');

        // Устанавливаем обработчик завершения скрипта
        register_shutdown_function('\Ideal\Core\Error::shutdownFunction');

        // Определяем корневую папку всей системы
        $root = stream_resolve_include_path($webRoot . '/../');

        // Загружаем данные из конфигов
        $this->load($root);
    }

    /**
     * Запуск FrontController'а
     *
     * Проводится роутинг, определяется контроллер страницы и отображаемый текст.
     * Выводятся HTTP-заголовки и отображается текст, сгенерированный с помощью view в controller
     */
    public function run(): void
    {
        // Инициализируем начальные $request, $response
        $request = $this->getRequest();

        // Запускаем обработку очереди middleware
        $resolver = static function ($class) {
            if ($class === false) {
                // Обработчик для конца очереди, возвращающий чистый Response
                return static function (ServerRequest $request, Runner $runner) {
                    return new Response();
                };
            }
            return new $class();
        };

        $config = Config::getInstance();
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

    /**
     * Метод для возможности переопределения конфига
     * @param $root
     */
    protected function load($root): Config
    {
        // Получаем объект конфигурации
        $config = Config::getInstance();
        // Загружаем конфигурационные данные
        $config->load($root);

        return $config;
    }
}
