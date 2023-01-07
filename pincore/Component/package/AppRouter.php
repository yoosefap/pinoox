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

namespace pinoox\component\package;

use pinoox\component\helpers\HelperString;
use pinoox\component\router\Router;
use pinoox\component\Url;
use pinoox\component\worker\Config;


class AppRouter
{
    public static function run()
    {
        // Route::test();
    }

    /**
     * @param null $url
     * @return AppLayer
     */
    public static function find($url = null): AppLayer
    {
        $apps = self::get();
        $packageName = null;
        $path = null;

        // set app default
        if (isset($apps['*'])) {
            if (App::stable($apps['*'])) {
                $packageName = $apps['*'];
            }
            unset($apps['*']);
        }

        // set app current
        $url = empty($url) ? Url::parts() : $url;
        $parts = !empty($url) ? HelperString::explodeDropping('/', $url) : [];

        foreach ($parts as $part) {
            if (isset($apps[$part])) {
                $package = $apps[$part];
                if (App::stable($package)) {
                    $path = $part;
                    $packageName = $package;
                    break;
                }
            }
        }

        $path = !empty($path) ? $path : '';
        return new AppLayer($path, $packageName);
    }

    /**
     * Set default route
     *
     * @param $packageName
     */
    public static function setDefault($packageName)
    {
        self::set('*', $packageName);
    }

    /**
     * Set route
     *
     * @param $url
     * @param $packageName
     */
    public static function set($url, $packageName)
    {
        Config::init('~app>router')
            ->set($url, $packageName)
            ->save();
    }

    /**
     * Delete route by URL
     *
     * @param $url
     */
    public static function delete(string $url)
    {
        Config::init('~app>router')
            ->delete($url)
            ->save();
    }

    /**
     * Delete route by Package Name
     *
     * @param $packageName
     */
    public static function deletePackage(string $packageName)
    {
        $routes = self::get();
        $keys = array_keys($routes, $packageName);
        foreach ($keys as $key) {
            unset($routes[$key]);
        }

        Config::init('~app>router')->data($routes)->save();
    }

    /**
     * Get routes
     *
     * @param string|null $value
     * @return array|mixed|null
     */
    public static function get(?string $value = null)
    {
        return Config::init('~app>router')
            ->get($value);
    }

    /**
     * Get routes by Package Name
     *
     * @param string $packageName
     * @return array|mixed|null
     */
    public static function getPackage(string $packageName): ?array
    {
        $routes = self::get();
        return array_filter($routes, function ($route) use ($packageName) {
            return $route === $packageName;
        });
    }

    /**
     * Exists a route by URL
     *
     * @param string $url
     * @return bool
     */
    public static function exists(string $url): bool
    {
        return !empty(self::get($url));
    }

    /**
     * Exists a route by Package Name
     *
     * @param string $packageName
     * @return bool
     */
    public static function existsPackage(string $packageName): bool
    {
        return !empty(self::getPackage($packageName));
    }
}