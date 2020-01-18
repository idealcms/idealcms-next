<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Composer;

use Composer\IO\IOInterface;
use \Composer\Script\Event;
use Ideal\Core\Config;
use Ideal\Core\Db;
use Ideal\Core\FrontController;

class Script
{
    /**
     * Скрипт, выполняемый после composer create-project
     *
     * @param Event $event
     */
    public static function postCreateProject(Event $event): void
    {
        $vendorDir = realpath($event->getComposer()->getConfig()->get('vendor-dir'));

        $io = $event->getIO();

        $domain = self::input($io, 'Domain name: ');
        $publicFolder = self::input($io, 'Public folder [www]: ', 'www');
        $adminFolder = self::input($io, 'Admin folder: ');
        $dbHost = self::input($io, 'Database host [localhost]: ', 'localhost');
        $dbName = self::input($io, 'Database name: ');
        $dbLogin = self::input($io, 'Database login: ');
        $dbPassword = self::input($io, 'Database password: ');
        $dbPrefix = self::input($io, 'Table prefix [i_]: ', 'i_');

        // Вносим правки в файлы конфигурации
        $vendorDir = realpath($event->getComposer()->getConfig()->get('vendor-dir'));
        $projectDir = dirname($vendorDir);

        $configFile = $projectDir . '/app/config/config.php';
        $config = file_get_contents($configFile);

        if ($publicFolder !== 'www') {
            // Если нужно переименовать публичную папку
            $publicFolder = trim($publicFolder, '/');
            rename($projectDir . '/www', $projectDir . '/' . $publicFolder);
        }

        $config = mb_ereg_replace(
            "'adminFolder' => '/admin',",
            "'adminFolder' => '/{$adminFolder}',",
            $config,
            'z'
        );
        $config = mb_ereg_replace(
            "'host' => getenv\('DB_HOST'\) \?: '(.*)',",
            "'host' => getenv('DB_HOST') ?: '{$dbHost}',",
            $config,
            'z'
        );
        $config = mb_ereg_replace(
            "'login' => getenv\('DB_LOGIN'\) \?: '(.*)',",
            "'login' => getenv('DB_LOGIN') ?: '{$dbLogin}',",
            $config,
            'z'
        );
        $config = mb_ereg_replace(
            "'password' => getenv\('DB_PASSWORD'\) \?: '(.*)',",
            "'password' => getenv('DB_PASSWORD') ?: '{$dbPassword}',",
            $config,
            'z'
        );
        $config = mb_ereg_replace(
            "'name' => getenv\('DB_NAME'\) \?: '(.*)',",
            "'name' => getenv('DB_NAME') ?: '{$dbName}',",
            $config,
            'z'
        );
        $config = mb_ereg_replace(
            "'prefix' => 'i_'",
            "'prefix' => '{$dbPrefix}'",
            $config,
            'z'
        );
        file_put_contents($configFile, $config);

        // Вносим правки в файл .htaccess
        $htaccessFile = $projectDir . '/' . $publicFolder . '/.htaccess';
        $htaccess = file_get_contents($htaccessFile);
        $htaccess = str_replace('[[DOMAIN]]', $domain, $htaccess);
        file_put_contents($htaccessFile, $htaccess);

        // Заполняем БД начальными данными
        self::dbUpdate($vendorDir, $publicFolder);

        echo 'Success!';
    }

    /**
     * Отображение приглашения и ождание ввода в консоли
     *
     * @param IOInterface $io
     * @param string $prompt Текст приглашения
     * @param string $default Значение по умолчанию (если нужно)
     * @return string Результат ввода пользователя
     */
    protected static function input(IOInterface $io, string $prompt, string $default = ''): string
    {
        do {
            $domain = $io->ask($prompt);
        } while (empty($domain) && empty($default));

        $domain = $domain ?? $default;

        return $domain;
    }

    protected static function dbUpdate(string $vendorDir, string $publicFolder): string
    {
        require_once $vendorDir . '/autoload.php';

        // Инициализируем фронт контроллер, после чего нам доступна вся конфигурация системы
        $projectDir = dirname($vendorDir);
        $page = new FrontController($projectDir . '/' . $publicFolder);

        $config = Config::getInstance();
        $db = Db::getInstance();

        // Создание таблиц аддонов в БД
        $addonDir = $vendorDir . '/idealcms/idealcms/src/Addon';
        if ($handle = opendir($addonDir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($addonDir . '/' . $file)) {
                        $table = $config->db['prefix'] . 'ideal_addon_' . strtolower($file);
                        $fields = require($addonDir . '/' . $file . '/config.php');
                        $db->create($table, $fields['fields']);
                    }
                }
            }
        }

        // Создание таблиц структур в БД
        foreach ($config->structures as $structure) {
            $class = $config->getClassName($structure['structure'], 'Structure', 'Install');
            $install = new $class();
            $install->do();
            print $class . "\n";
        }

        return '';
    }
}
