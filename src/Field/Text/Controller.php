<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Field\Text;

use Ideal\Field\AbstractController;

/**
 * Самое простое, однострочное текстовое поле
 *
 * Пример объявления в конфигурационном файле структуры:
 *     'name' => array(
 *         'label' => 'Заголовок',
 *         'sql'   => 'varchar(255) not null',
 *         'type'  => 'Ideal_Text'
 *     ),
 */
class Controller extends AbstractController
{

    /** @inheritdoc */
    protected static $instance;

    /**
     * {@inheritdoc}
     */
    public function getInputText()
    {
        $value = htmlspecialchars($this->getValue());
        return
            '<input type="text" class="form-control" name="' . $this->htmlName
            . '" id="' . $this->htmlName
            . '" value="' . $value . '">';
    }
}
