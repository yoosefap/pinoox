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

use pinoox\component\kernel\ContainerBuilder;
use pinoox\component\package\App;
use pinoox\component\worker\Config;
use pinoox\component\Dir;
use pinoox\component\Lang;
use pinoox\component\Service;
use pinoox\component\Url;
use pinoox\component\helpers\HelperString;
use pinoox\component\worker\Pinker;
use pinoox\component\kernel\Container;
use pinoox\component\template\ViewInterface;

if (!function_exists('url')) {
    function url($link = null)
    {
        return Url::link($link);
    }
}

if (!function_exists('furl')) {
    function furl($path = null)
    {
        return Url::file($path);
    }
}

if (!function_exists('path')) {
    function path($path = null, $app = null)
    {
        return Dir::path($path, $app);
    }
}

if (!function_exists('lang')) {
    function lang($var)
    {
        $args = func_get_args();
        $first = array_shift($args);

        $result = Lang::replace($first, $args);

        echo !is_array($result) ? $result : HelperString::encodeJson($result);
    }
}

if (!function_exists('rlang')) {
    function rlang($var)
    {
        $args = func_get_args();
        $first = array_shift($args);

        return Lang::replace($first, $args);
    }
}

if (!function_exists('config')) {
    /**
     * get or set config
     *
     * @param string $key
     * @return mixed|null
     */
    function config(string $key)
    {
        $parts = explode('.', $key);
        $name = array_unshift($parts);
        $key = implode('.', $parts);
        $config = Config::init($name);
        $args = func_get_args();
        if (isset($args[1]))
            $config->set($key, $args[1]);
        else
            $config->get($key);

        return null;
    }
}

if (!function_exists('service')) {
    function service($service)
    {
        return Service::run($service);
    }
}

if (!function_exists('app')) {
    function app($key = null)
    {
        return App::get($key);
    }
}

if (!function_exists('pinker')) {
    /**
     * Save data & info in pinker
     *
     * @param mixed $data
     * @param array $info
     * @return array
     */
    function pinker(mixed $data, array $info = []): array
    {
        return Pinker::build($data, $info);
    }
}

if (!function_exists('cache')) {
    /**
     * Cache data in pinker
     *
     * @param mixed $data
     * @param int $lifetime seconds
     * @return array
     */
    function cache(mixed $data, int $lifetime): array
    {
        $info = $lifetime ? ['lifetime' => $lifetime] : [];
        return Pinker::build($data, $info);
    }
}

if (!function_exists('view')) {
    /**
     * render view
     *
     * @param string $name
     * @param array $parameters
     * @return ViewInterface
     * @throws Exception
     */
    function view(string $name = '', array $parameters = []): ViewInterface
    {
        $view = Container::pincore()->get('view');

        if (!($view instanceof ViewInterface))
            throw new Exception('not found view class in the container');

        return $view->ready($name, $parameters);
    }
}

if (!function_exists('container')) {
    /**
     * Open app container
     *
     * @param string|null $packageName
     * @return ContainerBuilder
     */
    function container(?string $packageName = null): ContainerBuilder
    {
        return Container::app($packageName);
    }
}
