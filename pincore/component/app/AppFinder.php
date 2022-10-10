<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace pinoox\component\app;

use NunoMaduro\Collision\Adapters\Laravel\ExceptionHandler;
use pinoox\component\Config;
use pinoox\component\Dir;
use pinoox\component\HelperString;
use pinoox\component\Session;
use pinoox\component\Url;
use pinoox\model\UserModel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\DebugClassLoader;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;

class AppFinder
{
    private static $packageName = null;
    private static $isDefaultApp = false;
    private static $isDomain = false;
    private static $url = null;
    private static $appUrl = null;
    const app_folder = 'apps';

    public static function current()
    {
        $url = Url::parts(null,false);
        dd(self::byUrl($url));
    }

    private static function byUrl($url = null)
    {
        $new_url = (empty($url)) ? self::$url : $url;
        $parts = HelperString::explodeDropping('/', $new_url);
        foreach ($parts as $part) {
            $packageName = self::getPackageName($part);
            if (self::existPackage($packageName, true) && AppProvider::get('enable') && ((!self::$isDomain) || (self::$isDomain && AppProvider::get('domain')))) {
                self::$appUrl = $part;
                if (empty($url)) {
                    self::setApp($packageName);
                    self::$url = trim(HelperString::firstDelete($new_url, $part), '/');
                    return;
                } else {
                    $packageName = $packageName;
                    $new_url = trim(HelperString::firstDelete($new_url, $part), '/');
                    return ["app" => $packageName, "url" => $new_url];
                }
            }
        }

        $default_app = Config::get('~app.*');
        if (self::existPackage($default_app, true)) {
            if (empty($url)) {
                self::setApp($default_app);
                self::$isDefaultApp = true;
            } else {
                return ["app" => $default_app, "url" => $new_url];
            }
        }

        if (!empty($url)) return ["app" => "__NO_APP__", "url" => $new_url];
        if (empty(self::getApp())) {
            die("No app available!");
        }
    }

    public static function setApp($packageName)
    {
        $area = AppProvider::get('area');
        if (!empty($packageName)) {
            self::$packageName = $packageName;
        } else {
            self::$packageName = $area;
        }

        if (Session::isStartOnDatabase())
            Session::app(self::$packageName);
    }

    public static function getApp()
    {
        return self::$packageName;
    }

    public static function existPackage($packageName, $isBake = false)
    {
        $app_file = Dir::path('~' . self::app_folder . '/' . $packageName . "/app.php");
        if (file_exists($app_file)) {
            if ($isBake) {
                $packageName = self::$packageName;
                self::$packageName = $packageName;
                //self::setAppProvider($packageName);
                self::$packageName = $packageName;
            }

            return true;
        }
        return false;
    }

    private static function getPackageName($part)
    {
        if ($part === '*') return null;
        return Config::getLinear('~app', $part);
    }
}