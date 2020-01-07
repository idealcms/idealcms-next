<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru/)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Field\Hidden;

use Ideal\Field\AbstractController;

/**
 * Поле, недоступное для редактирования пользователем в админке.
 *
 * Отображается в виде скрытого поля ввода <input type="hidden" />
 *
 * Пример объявления в конфигурационном файле структуры:
 *     'date_create' => array(
 *         'label' => 'id родительских структур',
 *         'sql'   => 'char(15)',
 *         'type'  => 'Ideal_Hidden'
 *     ),
 */
class Controller extends AbstractController
{

    /** {@inheritdoc} */
    protected static $instance;

    /**
     * {@inheritdoc}
     */
    public function showEdit()
    {
        $this->htmlName = $this->groupName . '_' . $this->name;
        $input = $this->getInputText();
        return $input;
    }

    /**
     * {@inheritdoc}
     */
    public function getInputText()
    {
        return '<input type="hidden" id="' . $this->htmlName
        . '" name="' . $this->htmlName
        . '" value="' . $this->getValue() . '">';
    }
}
