<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Structure\Part\Site;


class Router extends \Ideal\Core\Site\Router
{
    /**
     * {@inheritdoc}
     */
    public function route(array $path, array $url): \Ideal\Core\Site\Router
    {
        $this->path = $path;

        // Определяем модель
        $this->model = new Model();
        $this->model->setPath($path);

        // todo роутинг внутри структуры Part и переход на вложенные структуры

        return $this;
    }
}
