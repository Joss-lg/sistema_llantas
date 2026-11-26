<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Datos de la empresa
    |--------------------------------------------------------------------------
    | El nombre sale de APP_NAME (.env). Para cambiar el nombre de la empresa
    | solo hay que cambiar esa línea. Los datos de contacto son opcionales:
    | si se dejan vacíos, no se muestran en el ticket.
    */

    'nombre' => env('APP_NAME', 'OllinLlantas'),

    'logo' => 'img/logo-ollintem.png',
    'logo_blanco' => 'img/logo-ollintem-blanco.png',
    'icono' => 'img/icono-ollintem.png',

    'telefono' => env('EMPRESA_TELEFONO'),
    'whatsapp' => env('EMPRESA_WHATSAPP'),

];