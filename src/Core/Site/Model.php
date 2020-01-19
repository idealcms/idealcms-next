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
        // Определяем файл шаблона
        $config = Config::getInstance();
        $rootFolder = $config->getRootFolder();
        $class = explode('\\', get_class($this));
        $places = ['/src/Ideal', '/vendor/idealcms/idealcms/src', '/src']; // todo определение папки для модулей
        $file = 'index.twig'; // todo сделать получение названия шаблона из соответствующего поля модели

        // Ищем, какой файл шаблона взять (кастомизированный, из вендора, или из корневой src [в тестовом окружении])
        foreach ($places as $place) {
            $templateFile = $rootFolder . $place . '/Structure/' . $class[2]. '/Site/' . $file;
            if (file_exists($templateFile)) {
                break;
            }
        }

        return [dirname($templateFile), basename($templateFile)];
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
