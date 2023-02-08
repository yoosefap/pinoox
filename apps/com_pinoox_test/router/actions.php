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

use function pinoox\router\{action};
use pinoox\app\com_pinoox_test\controller\MainController;


action('home', [MainController::class, 'home']);

action('home', 'MainController::home');

$test = 'ali';

action('home', function () use($test) {
    return view('home', [
        'title' => 'my page',
        'content' => 'hello ' . $test,
    ]);
});

//action('home', fn() => view('home', [
//    'title' => 'my page',
//    'content' => 'hello ' . $test,
//]));