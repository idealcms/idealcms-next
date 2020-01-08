<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core\Site;


use Ideal\Core\Config;

class Model
{
    /** @var array Элементы пути к странице */
    protected $path;

    /** @var array Все данные страницы */
    protected $pageData;

    /** @var string Название таблицы, в которой находятся данные структуры */
    protected $table;

    /**
     * Получение полного пути к файлу twig-шаблона
     *
     * @return array
     */
    public function getTemplate()
    {
        $config = Config::getInstance();
        $rootFolder = $config->getRootFolder();
        $class = explode('\\', get_class($this));
        // todo сделать получение названия шаблона из соответствующего поля модели
        $template = [
            $rootFolder . '/vendor/idealcms/idealcms/src/Structure/' . $class[2]. '/Site',
            'index.twig'
        ];

        return $template;
    }

    /**
     * Установка массива пути к запрошенной странице
     *
     * @param array $path
     */
    public function setPath(array $path): void
    {
        $this->path = $path;
    }

    /**
     * Установка всех данных страницы (обычно из БД)
     *
     * @param array $page
     */
    public function setPageData(array $page): void
    {
        $this->pageData = $page;
    }

    /**
     * Получение всех данных страницы
     *
     * @return array
     */
    public function getPageData(): array
    {
        // todo получение данных по аддонам страницы
        return $this->pageData;
    }
}
