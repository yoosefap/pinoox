<?php

use App\com_pinoox_manager\Flow\BootFlow;
use App\com_pinoox_manager\Flow\ManagerAuthFlow;

return [
    'package' => 'com_pinoox_manager',
    'enable' => true,
    'sys-app' => true,
    'theme' => 'spark',
    'hidden' => true,
    'name' => 'manager',
    'title' => 'Manager',
    'description' => 'Pinoox control panel',
    'icon' => '@layout-dashboard',
    'version-name' => '2.4.80',
    'version-code' => 103,
    'developer' => 'Pinoox Team',
    'minpin' => 2,
    'lang' => 'fa',
    'date' => 'jalali',
    'transport' => [
        'user' => 'platform',
    ],
    'filesystem' => [
        'disk' => 'local', // public disk ⇒ public uploads; anything else ⇒ private
        'hash_length' => 8, // hash_id length (4–50); shorter URLs, still unique-checked
        'file_policy' => 'owner',
        'groups' => [
            // 'avatar' => 'public',
            // 'docs' => 'login',
            // 'admin' => 'role:admin',
            // 'reports' => 'permission:reports.view',
        ],
        'thumb_width' => 512,
        'thumb_height' => 512,
    ],
    'auth' => [
        'mode' => 'jwt',
        'key' => 'manager_pinoox',
        'lifetime' => 30,
        'lifetime_unit' => 'day',
        'client' => true,
    ],
    'access' => [
        'super_roles' => ['admin'],
        'groups' => [
            'admin' => ['*'],
        ],
    ],
    'flow' => [
        BootFlow::class,
    ],
    'alias' => [
        'manager' => [
            'auth' => ManagerAuthFlow::class,
        ],
    ],
    'router' => [
        'routes' => [
            'routes/web.php',
        ],
    ],
    'pinx' => [
        'type' => 'app',
        'minpin' => 2,
    ],
    'build' => [
        'exclude' => ['node_modules', 'tests'],
    ],
];

