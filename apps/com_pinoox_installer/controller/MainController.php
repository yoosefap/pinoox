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

use pinoox\component\Dir;
use pinoox\component\File;
use pinoox\component\http\Request;
use pinoox\component\kernel\controller\Controller;
use pinoox\component\package\App;
use pinoox\component\Response;
use pinoox\component\router\Router;
use pinoox\component\router\Route;
use pinoox\component\template\engine\PhpTwigEngine;
use pinoox\component\template\View;
use Symfony\Component\Templating\DelegatingEngine;
use pinoox\component\template\engine\PhpEngine;
use pinoox\component\template\parser\TemplateNameParser;
use pinoox\component\template\engine\TwigEngine;

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

    public function _main(Request $request)
    {
        $folder = App::get('theme');
        $pathTheme = Dir::path(App::get('path-theme'));
        $loader = new \Twig\Loader\FilesystemLoader($folder, $pathTheme);

//        $loader = new \Twig\Loader\ArrayLoader([
//            'index.php' => (new Template($pathTheme,$folder))->getProcessedText('index'),
//        ]);
        $template = new \Twig\Environment($loader);
        return $template->render('index.php', ['test' => 'lang2']);
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
    
