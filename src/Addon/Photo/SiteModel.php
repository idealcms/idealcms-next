<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Addon\Photo;

use Ideal\Addon\AbstractSiteModel;
use Ideal\Core\Config;
use Ideal\Core\View;

class SiteModel extends AbstractSiteModel
{
    public function getPageData()
    {
        $this->setPageDataByPrevStructure($this->prevStructure);

        $config = Config::getInstance();

        $tplRoot = dirname(stream_resolve_include_path('Addon/Photo/index.twig'));
        $View = new View($tplRoot, $config->cache['templateSite']);
        $View->loadTemplate('index.twig');
        $View->images = json_decode($this->pageData['images']);
        $View->imagesRel = $this->fieldsGroup;
        $photoContent = $View->render();
        if (isset($this->pageData['content'])) {
            $this->pageData['content'] .= $photoContent;
        } else {
            $this->pageData['content'] = $photoContent;
        }

        return $this->pageData;
    }
}
