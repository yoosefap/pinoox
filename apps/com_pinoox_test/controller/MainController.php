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
use pinoox\component\kernel\Exception;
use pinoox\component\lang\Lang;
use pinoox\component\lang\source\FileLangSource;
use pinoox\portal\AppWizard;
use pinoox\portal\Config;
use pinoox\portal\DB;
use pinoox\portal\Path;
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
        $wizard = AppWizard::open(PINOOX_PATH . 'pins/dev_armin_comingsoon.pin');

        $wizard->getMeta();
        $wizard->getInfo();
        $wizard->isUpdateAvailable();
        $wizard->install();
        $wizard->migration()->force()->install();

        return '<h2> uncomment codes for testing "AppWizard" component</h2>';
    }

    /*
        public function template()
        {
            $wizard = TemplateWizard::open(PINOOX_PATH . 'installs\welcome.pin');

            return dd($wizard->getInfo(), $wizard->install());
        }*/

    public function config()
    {
        $cm = Config::name('test');
        dd($cm->get());
    }

    public function lang()
    {
        try {
            $path = Path::get('com_pinoox_test:lang');
            $lang = new Lang(new FileLangSource($path, 'fa'));
            $lang->setFallback('en');
            dd($lang->getChoice('user.apples', 2));
        } catch (Exception $e) {
        }
    }

    public function pinker()
    {
        $p2 = Config::name('test')->get();
        dd($p2);
    }

    public function query()
    {
        $result = DB::table('table_name')->get();
        dd($result);
    }

}
    
