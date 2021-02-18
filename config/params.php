<?php
return [
    'judgeProblemDataPath' => dirname(__FILE__) . '/../judge/data/',

    'polygonProblemDataPath' => dirname(__FILE__) . '/../polygon/data/',

    'components.formatter' => [
        'class' => app\components\Formatter::class,
        'defaultTimeZone' => 'Asia/Bangkok',
        'locale' => 'vi-VN',
        'dateFormat' => 'dd/MM/yyyy',
        'datetimeFormat' => 'dd/MM/tttt HH:mm:ss',
        'thousandSeparator' => '&thinsp;',
    ],
    'components.setting' => [
        'class' => app\components\Setting::class,
    ],
];
