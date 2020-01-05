<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/ideals/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Composer;

use \Composer\Script\Event;

class Script
{
    /**
     * Скрипт, выполняемый после composer create-project
     *
     * @param Event $event
     */
    public static function postCreateProject(Event $event): void
    {
        $composer = $event->getComposer();

        $io = $event->getIO();

        $domain = self::input($io, 'Domain name: ');
        $adminFolder = self::input($io, 'Admin folder: ');
        $dbHost = self::input($io, 'Database host [localhost]: ', 'localhost');
        $dbName = self::input($io, 'Database name: ');
        $dbLogin = self::input($io, 'Database login: ');
        $dbPassword = self::input($io, 'Database password: ');
        $dbPrefix = self::input($io, 'Table prefix [i_]: ', 'i_');

        // Вносим правки в файлы конфигурации
        $vendorDir = realpath($event->getComposer()->getConfig()->get('vendor-dir'));
        $projectDir = realpath($vendorDir . '/..');

        $configFile = $projectDir . '/app/config/config.php';
        $config = file_get_contents($configFile);

        $config = mb_ereg_replace(
            "'adminFolder' => '/admin',",
            "'adminFolder' => '/{$adminFolder}',",
            $config,
            'z'
        );
        $config = mb_ereg_replace(
            "'name' => getenv\('DB_HOST'\) \?: '(.*)',",
            "'name' => getenv('DB_HOST') ?: '{$dbHost}',",
            $config,
            'z'
        );
        $config = mb_ereg_replace(
            "'name' => getenv\('DB_LOGIN'\) \?: '(.*)',",
            "'name' => getenv('DB_LOGIN') ?: '{$dbLogin}',",
            $config,
            'z'
        );
        $config = mb_ereg_replace(
            "'name' => getenv\('DB_PASSWORD'\) \?: '(.*)',",
            "'name' => getenv('DB_PASSWORD') ?: '{$dbPassword}',",
            $config,
            'z'
        );
        $config = mb_ereg_replace(
            "'prefix' => 'i_'",
            "'prefix' => '{$dbName}'",
            $config,
            'z'
        );
        file_put_contents($configFile, $config);

        $htaccessFile = $projectDir . '/www/.htaccess';
        $htaccess = file_get_contents($htaccessFile);
        $htaccess = str_replace('[[DOMAIN]]', $domain, $htaccess);
        file_put_contents($htaccessFile, $htaccess);

        echo 'Success!';
    }

    /**
     *
     * @param $io
     * @param $prompt
     * @param string $default
     * @return string
     */
    protected static function input($io, $prompt, $default = ''): string
    {
        do {
            $domain = $io->ask($prompt);
        } while (empty($domain) && empty($default));

        $domain = $domain ?? $default;

        return $domain;
    }
}
