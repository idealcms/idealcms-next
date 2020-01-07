<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Medium\UserList;

use Ideal\Core\Config;
use Ideal\Core\Db;
use Ideal\Medium\AbstractModel;

/**
 * Медиум для получения списка пользователей системы
 */
class Model extends AbstractModel
{
    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $list = [0 => '---'];
        $db = Db::getInstance();
        $config = Config::getInstance();
        $table = $config->db['prefix'] . 'ideal_structure_user';
        $sql = 'SELECT id, email FROM ' . $table . ' ORDER BY email ASC';
        $arr = $db->select($sql);
        foreach ($arr as $item) {
            $list[$item['id']] = $item['email'];
        }
        return $list;
    }
}
