<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Structure\Home\Site;


use Ideal\Core\Config;
use Ideal\Core\Db;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;

class Router extends \Ideal\Core\Site\Router
{
    /**
     * {@inheritdoc}
     */
    public function route(array $path, array $url): \Ideal\Core\Site\Router
    {
        $this->path = $path;

        if (empty($url)) {
            // Запрошена главная страница, инициализируем модель
            $model = new Model();
            $model->setPath($this->path);
            $model->setPageByUrl('/');

            return $this;
        }

        // Находим главную начальную структуру и делаем роутинг от неё
        $config = Config::getInstance();
        $structure = $config->getStartStructure();
        $routerClass = $config->getClassName($structure['structure'], 'Structure', 'Site\\Router');
        /** @var \Ideal\Core\Site\Router $router */
        $router = new $routerClass($this->request, $this->response);
        $router = $router->route($path, $url);

        return $router;
    }
}
