<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Addon;

use Ideal\Core\Db;

/**
 * Абстрактный класс, реализующий основные методы для семейства классов Addon во внешней части сайта
 *
 * Аддоны обеспечивают прикрепление к структуре дополнительного содержимого различных типов.
 */
class AbstractSiteModel extends \Ideal\Core\Site\Model
{
    /**
     * {@inheritdoc}
     */
    public function detectPageByUrl($path, $url)
    {
    }

    public function setPageDataByPrevStructure($prevStructure)
    {
        $db = Db::getInstance();

        // Получаем идентификатор таба из группы
        list(, $tabId) = explode('-', $this->fieldsGroup, 2);
        $_sql = "SELECT * FROM {$this->_table} WHERE prev_structure=:ps AND tab_id=:tid";
        $pageData = $db->select($_sql, array('ps' => $prevStructure, 'tid' => $tabId));
        if (isset($pageData[0]['id'])) {
            // TODO сделать обработку ошибки, когда по prevStructure ничего не нашлось
            /** @noinspection PhpUndefinedMethodInspection */
            $this->setPageData($pageData[0]);
        }
    }
}
