<?php

return array(
    'name' => 'Отчеты продаж товаров по характеристикам',
    'description' => 'Позволяет сформировать отчеты продаж товаров в разрезе заданной характеристики',
    'vendor' => 985310,
    'version' => '1.0.0',
    'img' => 'img/salesfeatures.png',
    'shop_settings' => true,
    'frontend' => true,
    'handlers' => array(
        'backend_reports' => 'backendReports',
    ),
);
//EOF
