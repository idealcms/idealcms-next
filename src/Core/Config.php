<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core;

use RuntimeException;

/**
 * Класс конфигурации, в котором хранятся все конфигурационные данные CMS
 * @property array db Массив с настройками подключения к БД
 * @property string cmsFolder Название папки с CMS
 * @property string startUrl Начальная папка CMS
 * @property array middleware Очередь middleware
 * @property array definitions Список замен для классов
 * @property array structures Список используемых структур проекта
 */
class Config
{
    /** @var object Необходима для реализации паттерна Singleton */
    private static $instance;

    /** @var array Содержит все конфигурационные переменные проекта */
    private $array = [];

    /** @var array Хранилище созданных объектов */
    protected $created = [];

    /**
     * Статический метод, возвращающий находящийся в нём динамический объект
     *
     * Этот метод реализует паттерн Singleton.
     *
     * @return Config
     */
    public static function getInstance(): Config
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

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
    public function load(string $root): void
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
    public function import(array $arr): void
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

    /**
     * Из списка подключённых структур находит стартовую по наличию заполненного параметра startName
     *
     * @return array Массив стартовой структуры
     */
    public function getStartStructure(): array
    {
        foreach ($this->structures as $structure) {
            if (!empty($structure['startName'])) {
                return $structure;
            }
        }
        // Уведомление об ошибке, если нет структуры с startName
        throw new RuntimeException('Нет первой структуры');
    }

    /**
     * Создание объекта на основе списка переопределений
     *
     * @param string $name Название класса
     * @param array $args
     * @return object Созданный объект
     */
    public function create(string $name, ...$args): object
    {
        // Проверяем, нет ли класса в списке переопределений
        $name = $this->definitions[$name] ?? $name;

        return new $name(...$args);
    }

    /**
     * Создание и сохранение объекта на основе списка переопределений
     *
     * @param string $name Название класса
     * @param array $args
     * @return object Созданный объект
     */
    public function get(string $name, ...$args): object
    {
        // Проверяем, нет ли класса в списке переопределений
        $name = $this->definitions[$name] ?? $name;
        $object = $this->created[$name] ?? new $name(...$args);
        $this->created[$name] = $object;

        return $object;
    }

    /**
     * Установка подмены определённого класса
     *
     * @param string $name Имя подменяемого класса
     * @param string $substitute Имя класса-заместителя
     */
    public function set(string $name, string $substitute): void
    {
        $this->definitions[$name] = $substitute;
    }
}
