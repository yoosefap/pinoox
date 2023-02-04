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

use function pinoox\router\{get};
use pinoox\component\router\Router;
use pinoox\app\com_pinoox_installer\controller\MainController;

//
//get('/', [MainController::class, 'home']);

get(
    path: '/',
    action: [MainController::class, 'home'],
    name: 'home',
);

\pinoox\router\collection(
    routes: function (Router $router) {
    $router->add();
}
);