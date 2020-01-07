<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru/)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Field\ImageGallery;

use Ideal\Field\AbstractController;

/**
 * Поле редактирования фотогалереи
 *
 * Поле представляет из себя заголовок и кнопку выбора.
 * После выбора выстраивается список изображений с возможностью сортировки.
 *
 * Пример объявления в конфигурационном файле структуры:
 *     'img' => [
 *         'label' => 'Фотогалерея',
 *         'sql'   => 'mediumtext',
 *         'type'  => 'Ideal_ImageGallery'
 *     ],
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
        $value = htmlspecialchars($this->getValue());
        $html = <<<HTML
            <script type="text/javascript" src="Ideal/Field/ImageGallery/script.js"></script>
            <input class="images-values" type="hidden" id="{$this->htmlName}" name="{$this->htmlName}"
            value="{$value}">
            <div id="{$this->htmlName}-control-group">
                <div class="text-center"><strong>{$this->getLabelText()}</strong></div><br />
                <div class="text-center">
                    <span class="input-group-btn">
                        <button class="btn" onclick="imageGalleryShowFinder('{$this->htmlName}'); return false;">
                            Выбрать
                        </button>
                    </span>
                </div>
                <div id="{$this->htmlName}-list" class="input-group col-lg-12"></div>
            </div>
HTML;
        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getInputText()
    {
        return '';
    }
}
