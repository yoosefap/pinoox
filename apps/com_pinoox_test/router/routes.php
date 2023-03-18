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

//get(
//    path: '/test',
//    action: fn() => view('test test terst'),
//);