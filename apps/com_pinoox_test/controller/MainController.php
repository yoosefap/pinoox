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
use pinoox\component\kernel\Container;
use pinoox\component\kernel\controller\Controller;
use pinoox\portal\AppWizard;
use pinoox\portal\Config as config;
use pinoox\portal\TemplateWizard;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;

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

        $versionStrategy = new StaticVersionStrategy('v1');

        $defaultPackage = new Package($versionStrategy);

        $namedPackages = [
            'img' => new UrlPackage('https://img.example.com/', $versionStrategy),
            'doc' => new PathPackage('/somewhere/deep/for/documents', $versionStrategy, new RequestStackContext(Container::pincore()->get('request_stack'))),
        ];

        $packages = new Packages($defaultPackage, $namedPackages);


        echo $packages->getUrl('test') . "</br>";
        echo $packages->getUrl('test.png', 'img') . "</br>";
        echo $packages->getUrl('test.css', 'doc') . "</br>";
        exit;
        //  dd(AppEngine::routes('com_pinoox_test')->getMainCollection());
        $products = Product::all();
        $html = '<a href="' . $request->getBaseUrl() . '/add">add new</a>';
        foreach ($products as $product) {
            $html .= '<h2>' . $product->product_name . '</h2> <p>' . $product->summary . '</p>';
            $html .= '<br>';
        }
        return $html;

    }

    public function app()
    {
        $wizard = AppWizard::open(PINOOX_PATH . 'installs\com_pinoox_welcome.pin');

        try {
            return dd(
                $wizard->isUpdateAvailable(),
                $wizard->install(),
                $wizard->getMeta(),
                $wizard->getInfo()
            );
        } catch (ZipEntryNotFoundException $e) {
            return $e->getMessage();
        }
    }

    public function template()
    {
        $wizard = TemplateWizard::open(PINOOX_PATH . 'installs\welcome.pin');

        return dd($wizard->getInfo(), $wizard->install());
    }

    public function config()
    {
        $cm = config::name('~test');
        $cm->add('developers' ,['ali','ahmad']);
        dd($cm->get(),$cm->reset(),$cm->get(),$cm->restore());
    }
}
    