<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2019 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core;

/**
 * Класс конфигурации, в котором хранятся все конфигурационные данные CMS
 * @property array db Массив с настройками подключения к БД
 * @property string cmsFolder Название папки с CMS
 * @property string startUrl Начальная папка CMS
 * @property array middleware Очередь middleware
 */
class Config
{
    /** @var array Содержит все конфигурационные переменные проекта */
    private $array = [];

    /**
     * Возвращающает по запросу $config->varName переменную varName из массива $this->array
     *
     * @param string $name Название запрашиваемой переменной
     * @return string Значение запрашиваемой переменной
     */
    public function __get(string $name)
    {
        return $this->array[$name] ?? '';
    }

    /**
     * Устанавливает в $this->array переменную varName в указанное значение
     *
     * @param string $name  Название переменной
     * @param mixed  $value Значение переменной
     */
    public function __set(string $name, $value)
    {
        $this->array[$name] = $value;
    }

    /**
     * Определяет, установлена ли переменная в массиве array
     *
     * @param string $name Название переменной
     * @return bool
     */
    public function __isset(string $name)
    {
        return isset($this->array[$name]);
    }

    /**
     * Загружает все конфигурационные переменные из файлов config.php и settings.php
     * В дальнейшем доступ к ним осуществляется через __get этого класса
     *
     * @param string $root
     */
    public function load(string $root)
    {
        // Подключаем описание данных для БД
        /** @noinspection PhpIncludeInspection */
        $this->import(include $root . '/app/config/config.php');

        // Подключаем файл с переменными изменяемыми в админке
        /** @noinspection PhpIncludeInspection */
        $this->import(include $root . '/app/config/settings.php');
    }

    /**
     * Импортирует все значения массива $arr в массив $this->array
     *
     * @param array $arr Массив значений для импорта
     */
    public function import(array $arr)
    {
        // Проверяем, не объявлены ли переменные из импортируемого массива в этом классе
        foreach ($arr as $k => $v) {
            if (isset($this->$k)) {
                $this->$k = $v;
                unset($arr[$k]);
            }
        }
        // Объединяем импортируемый массив с основным массивом переменных конфига
        $this->array = array_merge($this->array, $arr);
    }
}
