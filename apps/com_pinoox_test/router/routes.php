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
route(
    path: '/pinker',
    action: 'MainController:pinker',
    methods: 'GET'
);
route(
    path: '/wizard',
    action: 'MainController:wizard',
    methods: 'GET'
);
route(
    path: '/query',
    action: 'MainController:query',
    methods: 'GET'
);
