<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Structure\Home\Site;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;

class Controller extends \Ideal\Core\Site\Controller
{
    /**
     * Действие по умолчанию - открытие главной страницы сайта
     */
    public function indexAction()
    {
        $this->view->header = 'Главная страница';
        $pageData = $this->model->getPageData();

        // Перенос данных страницы в шаблон
        foreach ($pageData as $k => $v) {
            $this->view->$k = $v;
        }
    }
}
