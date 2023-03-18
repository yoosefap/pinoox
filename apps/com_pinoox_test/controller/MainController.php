<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace pinoox\app\com_pinoox_test\controller;


use pinoox\component\http\Request;
use pinoox\component\kernel\controller\Controller;
use pinoox\portal\AppEngine;
use pinoox\portal\Config;
use pinoox\portal\Path;
use pinoox\portal\Pinker;


class MainController extends Controller
{
    public function home(Request $request)
    {
        dd(Path::app());

        return view('home', [
            'title' => 'yoosef',
            'content' => 'hello world! pinoox',
        ]);
    }
}
    
