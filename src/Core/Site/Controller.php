<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core\Site;

use Ideal\Core\Config;
use Ideal\Core\View;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;

class Controller
{
    /** @var Model Модель с данными страницы */
    protected $model;

    /** @var View */
    protected $view;

    /** @var ServerRequest Запрос, поступающий на вход контроллера */
    protected $request;

    /** @var Response Http-ответ контроллера */
    protected $response;

    /**
     * Controller constructor.
     * @param ServerRequest $request
     * @param Response $response Исходный объект http-ответа
     */
    public function __construct(ServerRequest $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Запуск контроллера для получения контента страницы
     *
     * @return Response Модифицированный объект ответа
     */
    public function run(): Response
    {
        $config = Config::getInstance();
        [$tplFolder, $tplName] = $this->model->getTemplate();
        $folders = [$tplFolder, $config->getRootFolder() . '/app/views'];
        $this->view = new View($folders, $config->cache['templateSite']);
        $this->view->loadTemplate($tplName);

        // Определяем и запускаем экшен
        $query = $this->request->getQueryParams();
        $action = $query['action'] ?? 'index';
        $action .= 'Action';
        $this->$action();

        // Twig рендерит текст странички из шаблона
        $text = $this->view->render();

        // Write to the response body:
        $this->response->getBody()->write($text);

        return $this->response;
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
