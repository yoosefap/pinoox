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
use pinoox\component\kernel\Boot;
use pinoox\component\kernel\controller\Controller;
use pinoox\component\package\App;
use pinoox\component\Response;
use pinoox\component\router\Router;
use pinoox\component\router\Route;

class MainController extends Controller
{
    public function test(Route $route,Request $request)
    {
        return 'okay test!';
       // dd('MainController:test');
       // return 'test';
       // return self::$template->render('test.twig',['test' => 'lang2']);
    }

    public function _main(Request $request)
    {
        return $this->forward('test');

        // dd($request);
      //  dd('main_installer');
       // self::$template->view('index');

       // return self::$template->render('test.twig',['test' => 'lang2']);
    }

    public function _exception()
    {
        Response::redirect(url('lang'));
    }


    public function lang()
    {
        $this->_main();
    }

    public function setup()
    {
        $this->_main();
    }

    public function rules()
    {
        $this->_main();
    }

    public function prerequisites()
    {
        $this->_main();
    }

    public function db()
    {
        $this->_main();
    }

    public function user()
    {
        Response::redirect(url('db'));
    }

    public function dist()
    {
        $url = implode('/', Router::params());
        if ($url === 'pinoox.js') {
            HelperHeader::contentType('application/javascript', 'UTF-8');
            self::$template->view('dist/pinoox.js');
        } else {
            $this->_main();
        }
    }
}
    
