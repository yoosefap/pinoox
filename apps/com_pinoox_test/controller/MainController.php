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

use pinoox\app\com_pinoox_test\model\Product;
use pinoox\component\http\Request;
use pinoox\component\kernel\controller\Controller;


class MainController extends Controller
{
    public function home(Request $request)
    {
        $p = new Product();
        $p->product_name = 'apple Iphone 14';
        $p->summary = 'best mobile around the world';
        $p->content = 'best mobile around the world......';
        $p->save();

        $products = Product::all();
        $html = '';
        foreach ($products as $product) {
            $html .= '<h2>' . $product->product_name . '</h2> <p>' . $product->summary . '</p>';
            $html .= '<br>';
        }
        return $html;

    }
}
    
