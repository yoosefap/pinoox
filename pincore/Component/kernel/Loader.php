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
        $loaders = spl_autoload_functions();

        foreach ($loaders as $l) {
            // we need to replace only composer
            if (is_array($l) && $l[0] instanceof ClassLoader) {
                spl_autoload_unregister($l);
            }
        }
       // spl_autoload_register([$this, 'loadClass'], true, true);

        self::loadServices();
    }

    private static function loadServices()
    {
        $services = Config::init('~service')->get();
        foreach ($services as $service) {
            Service::run($service);
        }
    }

    public function loadClass($className)
    {
        $result = $this->loader->loadClass($className);
        if($result === true) {
            //class loaded successfully
            $this->callConstruct($className);
            return true;
        }
        return null;
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