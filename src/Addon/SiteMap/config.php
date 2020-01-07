<?php
// Страница
return array(
    'params' => array(
        'name' => 'Карта сайта',
    ),
    'fields' => array(
        'id' => array(
            'label' => 'id',
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
        'level' => array(
            'label' => 'Кол-во отображаемых уровней',
            'sql' => 'int not null default 0',
            'type' => 'Ideal_Text'
        ),
        'disallow' => array(
            'label' => 'Регулярные выражения для отсеивания URL',
            'sql' => 'text',
            'type' => 'Ideal_Area',
            'help' => 'Регулярные выражения записываются по одному на каждую строку и обязательно '
                . 'с открывающими и закрывающими слэшами'
        ),
        'content' => array(
            'label' => 'Текст',
            'sql' => 'mediumtext',
            'type' => 'Ideal_RichEdit'
        ),
    )
);
