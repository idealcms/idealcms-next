<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru/)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Core;

/**
 * Класс вида View, обеспечивающий отображение переданных в него данных
 * в соответствии с указанным twig-шаблоном
 */
class View
{
    /** @var \Twig\Template */
    protected $template;

    /** @var \Twig\Environment * */
    protected $templater;

    /** @var array Массив для хранения переменных, передаваемых во View */
    protected $vars = array();

    /**
     * Инициализация шаблонизатора
     *
     * @param string|array $pathToTemplates Путь или массив путей к папкам, где лежат используемые шаблоны
     * @param bool $isCache
     */
    public function __construct($pathToTemplates, $isCache = false)
    {
        // Определяем корневую папку системы для подключение шаблонов из любой вложенной папки через их путь
        $config = Config::getInstance();
        /*
        $cmsFolder = DOCUMENT_ROOT . '/' . $config->cmsFolder;

        // Папки от которых строится путь до шаблона
        $idealFolders = array('Ideal.c', 'Ideal', 'Mods.c', 'Mods');
        foreach ($idealFolders as $k => $v) {
            if (file_exists($cmsFolder . '/' . $v)) {
                $idealFolders[$k] = $cmsFolder . '/' . $v;
            } else {
                unset($idealFolders[$k]);
            }
        }
        */

        $pathToTemplates = is_string($pathToTemplates) ? array($pathToTemplates) : $pathToTemplates;

        //$pathToTemplates = array_merge(array($cmsFolder), $pathToTemplates, $idealFolders);

        $loader = new \Twig\Loader\FilesystemLoader($pathToTemplates);

        $config = Config::getInstance();
        $params = array();
        if ($isCache) {
            $cachePath = $config->getTmpFolder() . '/templates';
            $params['cache'] = stream_resolve_include_path($cachePath);
            if ($params['cache'] === false) {
                Error::add('Не удалось определить путь для кэша шаблонов: ' . $cachePath);
                exit;
            }
        }
        $this->templater = new \Twig\Environment($loader, $params);
    }

    /**
     * Получение переменной View
     *
     * Передача по ссылке используется для того, чтобы в коде была возможность изменять значения
     * элементов массива, хранящегося во View. Например:
     *
     * $view->addonName[key]['content'] = 'something new';
     *
     * @param string $name Название переменной
     * @return mixed Переменная
     */
    public function &__get($name)
    {
        if (is_scalar($this->vars[$name])) {
            $property = $this->vars[$name];
        } else {
            $property = &$this->vars[$name];
        }
        return $property;
    }

    /**
     * Магический метод для проверки наличия запрашиваемой переменной
     *
     * @param string $name Название переменной
     * @return bool Инициализирована эта переменная или нет
     */
    public function __isset($name)
    {
        return isset($this->vars[$name]);
    }

    /**
     * Установка значения элемента, передаваемого во View
     *
     * @param string $name Название переменной
     * @param mixed $value Значение переменной
     */
    public function __set($name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * Загрузка в шаблонизатор файла с twig-шаблоном
     *
     * @param string $fileName Название twig-файла
     */
    public function loadTemplate(string $fileName): void
    {
        $this->template = $this->templater->load($fileName);
    }

    public function render()
    {
        return $this->template->render($this->vars);
    }

    /**
     * Чистит все файлы twig кэширования
     */
    public static function clearTwigCache($path = ''): void
    {
        $config = Config::getInstance();
        if (empty($path)) {
            $cachePath = $config->getTmpFolder() . '/templates';
        } else {
            $cachePath = $path;
        }
        if ($objs = glob($cachePath . '/*')) {
            foreach ($objs as $obj) {
                is_dir($obj) ? self::clearTwigCache($obj) : unlink($obj);
            }
        }
        if (!empty($path)) {
            rmdir($cachePath);
        }
    }
}
