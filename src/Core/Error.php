<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core;

use Exception;
use FB;
use Ideal\Mailer;

/**
 * Обработка ошибок в скриптах
 *
 */
class Error
{
    /** @var array Массив для хранения списка ошибок, возникших при выполнении скрипта */
    public static $errorArray = [];

    /**
     * Вывод сообщения об ошибке
     *
     * @param string $txt Текст сообщения об ошибке
     * @param $isTrace
     */
    public static function add($txt, $isTrace = true): void
    {
        $config = Config::getInstance();
        if (empty($config->cms['errorLog'])) {
            return;
        }
        $trace = [];
        $traceStr = $traceStrBr = '';
        if ($isTrace) {
            // Если нужно вывести путь до места совершения ошибки, строим его
            $traceList = debug_backtrace();
            array_shift($traceList); // убираем информацию о методе добавления ошибки
            foreach ($traceList as $item) {
                $file = str_replace($_SERVER['DOCUMENT_ROOT'], '', $item['file']);
                $trace[] = '#' . $item['line'] . ' in ' . $file . ' function ' . $item['function'] . PHP_EOL;
            }

            $traceStr = PHP_EOL . 'Trace:' . PHP_EOL . implode(PHP_EOL, $trace);
            $traceStrBr = PHP_EOL . 'Trace:' . PHP_EOL . implode('<br>', $trace);
        }
        switch ($config->cms['errorLog']) {
            case 'file':
                // Вывод сообщения в текстовый файл
                $msg = Date('d.m.y H:i', time()) . '  ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
                $msg .= $txt . $traceStr . PHP_EOL . PHP_EOL;
                $file = $config->getTmpFolder() . '/error.log';
                file_put_contents($file, $msg, FILE_APPEND);
                break;

            case 'display':
                // При возникновении ошибки, тут же её выводим на экран
                print $txt . $traceStrBr . '<br />';
                break;

            case 'comment':
                // При возникновении ошибки, выводим её закомментированно
                print '<!-- ' . $txt . $traceStr . ' -->' . PHP_EOL;
                break;

            case 'firebug':
                // Отображаем ошибку для просмотра через FireBug
                array_unshift($trace, $txt);
                try {
                    FB::error($trace);
                } catch (Exception $e) {
                    die('FireBug don`t work: ' . $txt);
                }
                break;

            case 'email':
            case 'var':
                self::$errorArray[] = $txt . $traceStr;
                break;

            default:
                break;
        }
    }

    /**
     * Метод, вызываемый после всех действий при завершении выполнения скрипта
     */
    public static function shutDown(): void
    {
        $error = error_get_last();
        $errors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING];

        if (in_array($error['type'], $errors, true)) {
            $errorStr = 'Ошибка ' . $error['message'] . ', в строке ' . $error['line'] . ' файла ' . $error['file'];
            self::add($errorStr, false);
        }

        $config = Config::getInstance();
        if ($config->cms['errorLog'] === 'email' && count(self::$errorArray) > 0) {
            if (empty($_SERVER['REQUEST_URI'])) {
                // Ошибка произошла при выполнении скрипта в консоли
                $source = 'При выполнении скрипта ' . $_SERVER['PHP_SELF'];
            } else {
                // Ошибка произошла при выполнеии скрипта в браузере
                $source = 'На странице ' . $_SERVER['REQUEST_SCHEME'] . '://'
                    . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            }

            $text = "Здравствуйте!\n\n{$source} произошли следующие ошибки.\n\n"
                . implode("\n\n", self::$errorArray) . "\n\n"
                . '$_SERVER = ' . "\n" . print_r($_SERVER, true) . "\n\n";
            if (isset($_GET)) {
                $text .= '$_GET = ' . "\n" . print_r($_GET, true) . "\n\n";
            }
            if (isset($_POST)) {
                $text .= '$_POST = ' . "\n" . print_r($_POST, true) . "\n\n";
            }
            if (isset($_COOKIE)) {
                $text .= '$_COOKIE = ' . "\n" . print_r($_COOKIE, true) . "\n\n";
            }
            $subject = 'Сообщение об ошибке на сайте ' . $config->domain;
            $mail = new Mailer();
            $mail->setSubj($subject);
            $mail->setPlainBody($text);
            $mail->sent($config->robotEmail, $config->cms['adminEmail']);
        }
    }

    /**
     * Обработчик обычных ошибок скриптов. Реакция зависит от настроек $config->errorLog
     *
     * @param int $errno   Номер ошибки
     * @param string $errstr  Сообщение об ошибке
     * @param string $errfile Имя файла, в котором была ошибка
     * @param int $errline Номер строки на которой произошла ошибка
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline): void
    {
        $error = 'Ошибка [' . $errno . '] ' . $errstr . ', в строке ' . $errline . ' файла ' . $errfile;
        self::add($error, false);
    }
}
