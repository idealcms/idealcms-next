<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Structure\Home\Site;


use Ideal\Core\Config;
use Ideal\Core\Db;
use Ideal\Core\Error;

class Model extends \Ideal\Core\Site\Model
{
    /**
     * Инициализация параметров модели
     */
    public function __construct()
    {
        $config = Config::getInstance();
        $this->table = $config->db['prefix'] . 'ideal_structure_part';
    }

    /**
     * Получение данных о странице из БД по url
     *
     * @param string $url
     */
    public function setPageByUrl(string $url): void
    {
        $db = Db::getInstance();

        $sql = "SELECT * FROM {$this->table} WHERE url=:url";
        $page = $db->select($sql, ['url' => $url]);
        if (empty($page[0]['id'])) {
            Error::add('В БД в таблице ' . $this->table . ' нет страницы с url:" ' . $url);
            return;
        }
        $this->setPageData($page[0]);
    }
}
