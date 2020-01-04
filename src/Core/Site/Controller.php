<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core\Site;

use Laminas\Diactoros\Response;

class Controller
{
    /** @var string Действие */
    protected $action;

    /** @var Model Модель с данными страницы */
    protected $model;

    /**
     * Запуск контроллера для получения контента страницы
     *
     * @param Response $response Исходный объект http-ответа
     * @return Response Модифицированный объект ответа
     */
    public function run(Response $response): Response
    {
        $content = 'Главная страница';

        // Write to the response body:
        $response->getBody()->write($content);

        return $response;
    }

    /**
     * Установка экшена для контроллера
     *
     * @param string $action Название экшена
     */
    public function setAction($action): void
    {
        $this->action = $action;
    }

    /**
     * Установка модели страницы
     *
     * @param Model $model Инициализированная модель страницы
     */
    public function setModel($model): void
    {
        $this->model = $model;
    }
}
