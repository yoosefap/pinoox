<?php

return [
    'package' => '__PINX_PACKAGE__',
    'name' => '__PINX_DISPLAY_NAME__',
    'description' => '__PINX_DESCRIPTION__',
    'developer' => '__PINX_DEVELOPER__',
    'icon' => 'resource/icon.png',
    'version-name' => '1.0.0',
    'version-code' => 1,
    'enable' => true,
    'theme' => 'default',
    'lang' => 'en',
    'router' => [
        'routes' => [
            'routes/web.php',
            'routes/actions.php',
        ],
    ],
    'pinx' => [
        'type' => 'app',
        'minpin' => 3,
        'sign' => [
            'enabled' => false,
            'key' => null,
            'key_id' => null,
            'require' => false,
        ],
    ],
];
