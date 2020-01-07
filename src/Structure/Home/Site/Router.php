<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Structure\Home\Site;


use Ideal\Core\Config;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;

class Router extends \Ideal\Core\Site\Router
{
    /** @var ServerRequest */
    protected $request;

    /** @var Response Изменяемый объект ответа */
    protected $response;

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
     * Определение контроллера, модели и action
     *
     * @return \Ideal\Core\Site\Controller
     * @throws \Exception
     */
    public function getController(): \Ideal\Core\Site\Controller
    {
        // Определяем модель
        $requestUri = $this->request->getServerParams()['REQUEST_URI'];
        $model = $this->detectPageByUrl([], $requestUri);

        // Определяем контроллер по модели
        $controller = mb_ereg_replace('Model$', 'Controller', get_class($model));

        /** @var \Ideal\Core\Site\Controller $controller */
        $controller = new $controller();

        // Определяем экшен
        $query = $this->request->getQueryParams();
        $action = $query['action'] ?? 'index';

        // Инициализируем контроллер
        $controller->setAction($action);
        $controller->setModel($model);

        return $controller;
    }

    /**
     * Определение страницы по URL
     *
     * @param array $path Разобранная часть URL
     * @param array $url Оставшаяся, неразобранная часть URL
     * @return \Ideal\Core\Site\Model
     * @throws \Exception
     */
    public function detectPageByUrl($path, $url): \Ideal\Core\Site\Model
    {
        $config = Config::getInstance();

        $structure = $config->getStartStructure();
        $structureClass = $config->getClassName($structure['structure'], 'Structure', 'Site\\Model');
        $model = new $structureClass();

        return $model;
    }

}
