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

use PhpZip\Exception\ZipEntryNotFoundException;
use pinoox\app\com_pinoox_test\model\Product;
use pinoox\component\http\Request;
use pinoox\component\kernel\controller\Controller;
use pinoox\portal\AppWizard;

class MainController extends Controller
{

    public function add(Request $request)
    {
        $p = new Product();
        $p->product_name = 'apple Iphone 14';
        $p->summary = 'best mobile around the world';
        $p->content = 'best mobile around the world......';
        $p->save();

        return '<h2>New Product Added</h2> <br> <a href="' . $request->getBaseUrl() . '">show list</a>';
    }

    public function home(Request $request)
    {
        $products = Product::all();
        $html = '<a href="' . $request->getBaseUrl() . '/add">add new</a>';
        foreach ($products as $product) {
            $html .= '<h2>' . $product->product_name . '</h2> <p>' . $product->summary . '</p>';
            $html .= '<br>';
        }
        return $html;

    }

    public function wizard()
    {
        $wizard = AppWizard::open(PINOOX_PATH . 'installs\com_pinoox_welcome.pin');

        return dd(
            $wizard->isUpdateAvailable(),
            $wizard->install(),
            $wizard->getMeta(),
            $wizard->getInfo()
        );
    }
}
    
