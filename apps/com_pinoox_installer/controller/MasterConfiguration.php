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

use pinoox\component\package\App;
use pinoox\component\package\AppRouter;
use pinoox\component\package\AppSource;
use pinoox\component\Dir;
use pinoox\component\helpers\HelperString;
use pinoox\component\interfaces\ControllerInterface;
use pinoox\component\Response;
use pinoox\component\Template;

class MasterConfiguration
{
    protected static $template;

    public function __construct()
    {
//        $folder = App::get('theme');
//        $pathTheme = Dir::path(App::get('path-theme'));
//        $loader = new \Twig\Loader\FilesystemLoader($folder,$pathTheme);
//        $loader->addPath('test');
//        self::$template = new \Twig\Environment($loader);
       // $this->getAssets();
        //$this->setLang();
    }

    private function setLang()
    {
        $lang = App::get('lang');
        $direction = in_array($lang, ['fa', 'ar']) ? 'rtl' : 'ltr';
        $data = HelperString::encodeJson([
            'install' => rlang('install'),
            'user' => rlang('user'),
            'language' => rlang('language'),
        ], true);
        self::$template->set('_lang', $data);
        self::$template->set('_direction', $direction);
        self::$template->set('currentLang', $lang);
    }

    private function getAssets()
    {
        $css = 'main.css';
        $js = 'main.js';
        $path = Dir::theme('dist/manifest.json');
        if (is_file($path)) {
            $manifest = file_get_contents($path);
            $manifest = HelperString::decodeJson($manifest)['main'];

            foreach ($manifest as $item) {
                if (HelperString::has($item, 'main.js'))
                    $js = $item;
                else if (HelperString::has($item, 'main.css'))
                    $css = $item;
            }
        }
        self::$template->assets = ['js' => $js, 'css' => $css];
    }


    public function _exception()
    {
        Response::redirect(url());
    }

    public function _404()
    {
        Response::redirect(url());
        exit;
    }
}