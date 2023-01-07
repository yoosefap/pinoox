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

use pinoox\component\worker\Config;
use pinoox\component\Service;

class Loader
{
    public static function boot()
    {
        self::loadServices();
    }

    private static function loadServices()
    {
        $services = Config::init('~service')->get();
        foreach ($services as $service) {
            Service::run($service);
        }
    }
}