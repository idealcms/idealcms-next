<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Medium\UserGroupList;

use Ideal\Core\Config;
use Ideal\Core\Db;
use Ideal\Medium\AbstractModel;

/**
 * Медиум для получения списка групп пользователей
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
        $table = $config->db['prefix'] . 'ideal_structure_usergroup';
        $sql = 'SELECT id, name FROM ' . $table . ' ORDER BY name ASC';
        $arr = $db->select($sql);
        foreach ($arr as $item) {
            $list[$item['id']] = $item['name'];
        }
        return $list;
    }
}
