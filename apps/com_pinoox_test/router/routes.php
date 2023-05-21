<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */

use function pinoox\router\{route};

route(
    path: '/',
    action: 'MainController:home',
    methods: 'GET'
);
route(
    path: '/add',
    action: 'MainController:add',
    methods: 'GET'
);
route(
    path: '/app',
    action: 'MainController:app',
    methods: 'GET'
);
route(
    path: '/template',
    action: 'MainController:template',
    methods: 'GET'
);
route(
    path: '/config',
    action: 'MainController:config',
    methods: 'GET'
);
route(
    path: '/configPortal',
    action: 'MainController:configPortal',
    methods: 'GET'
);
route(
    path: '/lang',
    action: 'MainController:lang',
    methods: 'GET'
);

//get(
//    path: '/test',
//    action: fn() => view('test test terst'),
//);