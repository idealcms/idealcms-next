<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru/)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Field\DateSet;

use Ideal\Field\Date;

/**
 * Поле, содержащее дату в формате timestamp, устанавливающуюся в текущую дату при создании элемента
 *
 * При открытии окна создания элемента с этим полем, в этом поле будет установлена актуальная дата
 *
 * Пример объявления в конфигурационном файле структуры:
 *     'date_create' => array(
 *         'label' => 'Дата создания',
 *         'sql'   => 'int(11) not null',
 *         'type'  => 'Ideal_DateSet'
 *     ),
 */
class Controller extends Date\Controller
{

    /** {@inheritdoc} */
    protected static $instance;

    /** @var bool Флаг необходимости получить текущую дату и время, либо считывать сохранённые из БД */
    protected $getNow = false;

    /**
     * {@inheritdoc}
     */
    public function getInputText()
    {
        $this->getNow = true;
        $html = parent::getInputText();
        $this->getNow = false;
        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $value = parent::getValue();
        if ($this->getNow && $value == '') {
            $value = time();
        }
        return $value;
    }
}
