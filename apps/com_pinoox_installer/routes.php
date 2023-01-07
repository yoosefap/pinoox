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

use function pinoox\router\{route, action, collection, get};
use pinoox\app\com_pinoox_installer\controller\MainController;

action('main', [MainController::class, '_main']);
action('test', [MainController::class, 'test']);

route('/test','@main','test');

collection(
    path: '/A',
    controller: MainController::class,
    methods: 'GET|POST',
    routes: 'web.php',
    filters: [],
    defaults: [],
    prefixName: 'A_',
);