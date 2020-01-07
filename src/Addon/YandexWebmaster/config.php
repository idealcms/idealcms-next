<?php
// ЯндексВебмастер
return array(
    'params' => array(
        'name' => 'Я.Вебмастер',
    ),
    'fields' => array(
        'id' => array(
            'label' => 'Идентификатор',
            'sql' => 'int(8) unsigned not null auto_increment primary key',
            'type' => 'Ideal_Hidden'
        ),
        'prev_structure' => array(
            'label' => 'id родительских структур',
            'sql' => 'char(15)',
            'type' => 'Ideal_Hidden'
        ),
        'tab_id' => array(
            'label' => 'id таба аддона',
            'sql' => 'int not null default 0',
            'type' => 'Ideal_Hidden'
        ),
        'yandexwebmastertext' => array(
            'label' => 'Текст для отправки в Яндекс.Вебмастер',
            'sql' => 'mediumtext',
            'type' => 'Ideal_YandexWebmasterText'
        ),
    )
);
