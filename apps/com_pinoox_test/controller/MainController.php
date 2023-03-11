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


use pinoox\component\Dir;
use pinoox\component\package\manager\AppManager;
use pinoox\portal\Data;
use pinoox\component\http\Request;
use pinoox\component\kernel\controller\Controller;
use pinoox\portal\Finder;
use pinoox\portal\Path;
use pinoox\portal\Pinker;


class MainController extends Controller
{
    public function home(Request $request)
    {
        $pinker = Pinker::file('app.php');
        $app = new AppManager($pinker);

        dump( $app->get());

        $app->set('test','Okay');
        $app->save();

        dd( $app->get());

        $path = Dir::path('~apps/');
        $appFiles1 = Finder::create()
            ->files()
            ->depth(1)
            ->name('app.php')
            ->in($path);

        $appFiles2 = Finder::create()
            ->files()
            ->depth(1)
            ->in($path);

        dd($appFiles1, $appFiles2);
        foreach ($appFiles as $appFile) {
            dd($appFile);
        }
        $path = Path::get('pincore:icon.png');

        if (is_file($path))

            // apps/sdfgsdfgh/config

            dd();
        return view('home', [
            'title' => 'yoosef',
            'content' => 'hello world! pinoox',
        ]);
    }
}
    
