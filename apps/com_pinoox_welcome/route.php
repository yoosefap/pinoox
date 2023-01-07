<?php

use pinoox\app\com_pinoox_welcome\controller\MasterConfiguration;
use pinoox\app\com_pinoox_welcome\controller\MainController;


use function pinoox\router\{collection, route, get, post};

route(
    path: '/',
    action: 'MainController:main',
);

route(
    path: '/search',
    action: 'MainController:search',
    method: 'GET|POST'
);

get(
    path: '/about',
    action: 'MainController:about',
);

post(
    path: '/getTitle',
    action: 'MainController:getTitle',
);


collection(
    path: '/blog',
    controller: 'BlogController',
    routes: function () {

    route(
        path: '/',
        action: 'blog',
        method: 'GET',
    );

    route(
        path: '/post/{post_id}',
        action: 'post',
        method: 'GET',
    );

});



