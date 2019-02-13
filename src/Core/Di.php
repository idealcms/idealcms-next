<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2019 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core;

/**
 * Класс контейнера Dependency Injection
 */
class Di extends \DI\Container
{

    /** @var object Необходима для реализации паттерна Singleton */
    private static $instance;

    /**
     * Статический метод, возвращающий находящийся в нём динамический объект
     *
     * Этот метод реализует паттерн Singleton.
     *
     * @param string $file Путь к файлу с переопределением зависимостей
     * @param string $cache Путь к папке для кэширования зависимостей
     * @param string $proxies Путь к папке для кэширования lazy-объектов
     * @return \Di\Container
     * @throws \Exception
     */
    public static function getInstance($file = '', $cache = '', $proxies = ''): \Di\Container
    {
        if (empty(self::$instance)) {
            $builder = new \DI\ContainerBuilder();
            // Подключение файла с переопределением классов
            if (!empty($file) && file_exists($file)) {
                $builder->addDefinitions($file);
            }

            // Настройка для production окружения, когда важна производительность
            if (!empty($cache)) {
                $builder->enableCompilation($cache);
            }
            if (!empty($proxies)) {
                $builder->writeProxiesToFile(true, $proxies);
            }

            self::$instance = $builder->build();
        }
        return self::$instance;
    }
}
