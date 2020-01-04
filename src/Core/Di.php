<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core;

/**
 * Класс контейнера Dependency Injection
 */
class Di
{
    /** @var object Необходима для реализации паттерна Singleton */
    private static $instance;

    /** @var array Список замен для классов */
    protected $definitions = [];

    /** @var array Хранилище созданных объектов */
    protected $created = [];

    /**
     * Статический метод, возвращающий находящийся в нём динамический объект
     *
     * Этот метод реализует паттерн Singleton.
     *
     * @param string $file Путь к файлу с переопределением зависимостей
     * @return Di
     */
    public static function getInstance($file = ''): Di
    {
        if (empty(self::$instance)) {
            $config = new self();

            // Подключение файла с переопределением классов
            if (!empty($file) && file_exists($file)) {
                $config->setDefinition($file);
            }

            self::$instance = $config;

        }
        return self::$instance;
    }

    /**
     * Считываем список переопределённых классов из файла
     *
     * @param string $file Путь к файлу с переопределениями
     */
    public function setDefinition(string $file): void
    {
        /** @noinspection PhpIncludeInspection */
        $this->definitions = require $file;
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
