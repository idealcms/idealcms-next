<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core;

use RuntimeException;

/**
 * Класс конфигурации, в котором хранятся все конфигурационные данные CMS
 * @property array db Массив с настройками подключения к БД
 * @property string adminFolder Название папки с CMS
 * @property string startUrl Начальная папка CMS
 * @property string domain Домен сайта
 * @property string robotEmail Почтовый ящик, с которого будут приходить письма с сайта
 * @property array cms Блок параметров CMS
 * @property array cache Блок параметров кэширования
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

    /** @var string Путь к папке с временными файлами */
    protected $tmpFolder = '';

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

        // Загрузка данных из конфигурационных файлов подключённых структур
        $this->loadStructures($root);
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
     * Загрузка в конфиг данных из конфигурационных файлов подключённых структур
     */
    protected function loadStructures($root): void
    {
        // Проходимся по всем конфигам подключённых структур и добавляем их в общий конфиг
        $structures = $this->structures;
        foreach ($structures as $k => $structureName) {
            list($module, $structure) = explode('_', $structureName['structure'], 2);
            $module = ($module === 'Ideal') ? '' : $module . '/';
            $fileName = $root . '/vendor/idealcms/idealcms/src/' . $module . 'Structure/' . $structure . '/config.php';
            /** @noinspection PhpIncludeInspection */
            $arr = require_once($fileName);
            if (is_array($arr)) {
                $structures[$k] = array_merge($structureName, $arr);
            }
        }

        // Строим массив соответствия порядковых номеров структур их названиям
        $structuresNum = array();
        foreach ($structures as $num => $structure) {
            $structureName = $structure['structure'];
            if (isset($structuresNum[$structureName])) {
                Error::add('Повторяющееся наименование структуры; ' . $structureName);
            }
            $structuresNum[$structureName] = $num;
        }

        // Проводим инъекции данных в соответствии с конфигами структур
        foreach ($structures as $structure) {
            if (!isset($structure['params']['in_structures'])) {
                // Пропускаем структуры, в которых не заданы инъекции
                continue;
            }
            foreach ($structure['params']['in_structures'] as $structureName) {
                $num = $structuresNum[$structureName];
                $structures[$num]['params']['structures'][] = $structure['structure'];
            }
        }
        $this->structures = $structures;
    }

    /**
     * Построение названия класса на основе названия структуры
     *
     * @param string $structure
     * @param string $type
     * @param string $class
     * @return string
     */
    public function getClassName(string $structure, string $type, string $class): string
    {
        [$module, $thing] = explode('_', $structure);
        return '\\' . $module . '\\' . $type . '\\' . $thing . '\\' . $class;
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
     * Из списка подключённых структур находит структуру на основании имени её класса
     *
     * @param string $className
     *
     * @return array|bool Массив структуры с указанным Id, или FALSE, если структуру не удалось обнаружить
     */
    public function getStructureByClass($className)
    {
        $className = trim($className, '\\');
        $classNameArr = explode('\\', $className);
        $className = $classNameArr[0] . '_' . $classNameArr[2];

        return $this->getStructureByName($className);
    }

    /**
     * Из списка подключённых структур находит структуру с нужным кратким наименованием
     *
     * @param string $name Краткое наименование структуры, например, Ideal_Part или Ideal_News
     *
     * @return array|bool Массив структуры с указанным названием, или FALSE, если структуру не удалось обнаружить
     */
    public function getStructureByName($name)
    {
        // TODO что делать, если с таким именем определено несколько структур
        // TODO сделать уведомление об ошибке, если такой структуры нет
        foreach ($this->structures as $structure) {
            if ($structure['structure'] === $name) {
                return $structure;
            }
        }
        return false;
    }

    /**
     * Из списка подключённых структур находит структуру на основе prev_structure
     *
     * @param string $prevStructure
     *
     * @return array|bool Массив структуры с указанным Id, или FALSE, если структуру не удалось обнаружить
     */
    public function getStructureByPrev($prevStructure)
    {
        $prev = explode('-', $prevStructure);
        if ($prev[0] == 0) {
            $structureId = $prev[1];
        } else {
            $structureId = $prev[0];
        }
        return $this->getStructureById($structureId);
    }

    /**
     * Из списка подключённых структур находит структуру с нужным идентификатором Id
     *
     * @param int $structureId Id искомой структуры
     *
     * @return array|bool Массив структуры с указанным Id, или FALSE, если структуру не удалось обнаружить
     */
    public function getStructureById($structureId)
    {
        // TODO сделать уведомление об ошибке, если такой структуры нет
        foreach ($this->structures as $structure) {
            if ($structure['id'] == $structureId) {
                return $structure;
            }
        }
        return false;
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

    /**
     * Получение полного пути к папке для хранения временных файлов
     *
     * @return string
     */
    public function getTmpFolder(): string
    {
        if (empty($this->tmpFolder)) {
            // todo получение папки временных файлов при запуске из консоли
            $this->tmpFolder = dirname($_SERVER['DOCUMENT_ROOT']) . $this->cms['tmpFolder'];
            if (!is_dir($this->tmpFolder)) {
                if (!mkdir($this->tmpFolder) && !is_dir($this->tmpFolder)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $this->tmpFolder));
                }
            }
        }

        return $this->tmpFolder;
    }

    /**
     * Получение полного пути к корню системы
     *
     * @return string
     */
    public function getRootFolder(): string
    {
        if (empty($this->rootFolder)) {
            $this->rootFolder = dirname($_SERVER['DOCUMENT_ROOT']);
        }

        return $this->rootFolder;
    }
}
