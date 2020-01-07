<?php
// Страница
return array(
    'params' => array(
        'name' => 'PHP-файл',
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
        'php_file' => array(
            'label' => 'Подключаемый файл',
            'sql' => 'varchar(255)',
            'type' => 'Ideal_Text'
        ),
        'content' => array(
            'label' => 'Текст',
            'sql' => 'mediumtext',
            'type' => 'Ideal_RichEdit'
        ),
    )
);
