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

namespace pinoox\app\com_pinoox_installer\controller;

use pinoox\component\http\Request;
use pinoox\component\kernel\controller\Controller;
use pinoox\portal\View;


class MainController extends Controller
{
    public function home(Request $request)
    {
        View::set('home', 'test');

        View::render('home', [
            'content' => 'hello world! pinoox',
        ]);


        View::get('home');

        dd(View::__history());

        return view('home', [
            'content' => 'hello world! pinoox',
        ]);
    }
}
    
