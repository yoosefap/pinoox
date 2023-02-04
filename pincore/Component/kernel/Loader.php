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

namespace pinoox\component\kernel;

use Composer\Autoload\ClassLoader;
use pinoox\component\worker\Config;
use pinoox\component\Service;

class Loader
{
    public static function boot(ClassLoader $loader)
    {
        $manager = new LoaderManager($loader);
        self::loadServices();
    }

    private static function loadServices()
    {
        $services = Config::init('~service')->get();
        foreach ($services as $service) {
            Service::run($service);
        }
    }

    /**
     * Proxy all method calls to Composer loader
     *
     * @param string $name
     * @param mixed $arguments
     */
    public function __call($name, $arguments)
    {
        call_user_func_array([$this->loader, $name], $arguments);
    }
}