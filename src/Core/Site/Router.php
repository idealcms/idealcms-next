<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core\Site;


use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;

abstract class Router
{
    /** @var ServerRequest */
    protected $request;

    /** @var Response Изменяемый объект ответа */
    protected $response;

    /** @var Model Модель, определённая роутером */
    protected $model;

    /**
     * Конструктор, инициализирующий в объекте Запрос и Ответ
     *
     * @param ServerRequest $request Объект http-запроса
     * @param Response $response Объект http-ответа
     */
    public function __construct(ServerRequest $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Определение роутера для запрашиваемой страницы
     *
     * @param array $path Построенная часть пути к запрошенной странице
     * @param array $url Неразобранная часть пути к запрошенной странице
     * @return Router Определённый роутер
     */
    abstract public function route(array $path, array $url): self;

    /**
     * Инициализация контроллера для этого роутера
     *
     * @return \Ideal\Core\Site\Router
     */
    public function getController(): Controller
    {
        // Определяем класс контроллера по модели
        $controllerClass = mb_ereg_replace('Model$', 'Controller', get_class($this->model));

        /** @var Controller $controller Инициализируем контроллер соответствующей структуры */
        $controller = new $controllerClass($this->request, $this->response);

        // Инициализируем контроллер
        $controller->setModel($this->model);

        return $controller;
    }
}
