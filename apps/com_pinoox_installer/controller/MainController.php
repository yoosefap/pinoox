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
use pinoox\component\Response;
use pinoox\component\router\Router;
use pinoox\component\router\Route;
use pinoox\portal\View1;


class MainController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->view->set('_url', url('test/'));
    }

    public function test(Route $route, Request $request)
    {
        return $this->view->render('test.twig', [
            'test' => 'Yoosef',
        ]);
    }

    public function _main()
    {
        return View1::fsdfsdf();
       //return 'testr';
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
    
