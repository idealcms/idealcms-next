<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Structure\Part\Site;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;

class Controller extends \Ideal\Core\Site\Controller
{
    /**
     * Действие по умолчанию - открытие запрошенной страницы структуры Part
     */
    public function indexAction()
    {
        $this->view->header = 'Внутренняя страница';
    }
}
