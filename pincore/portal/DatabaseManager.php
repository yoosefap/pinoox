<?php

/**
 * ***  *  *     *  ****  ****  *    *
 *   *  *  * *   *  *  *  *  *   *  *
 * ***  *  *  *  *  *  *  *  *    *
 *      *  *   * *  *  *  *  *   *  *
 *      *  *    **  ****  ****  *    *
 *
 * @author   Pinoox
 * @link https://www.pinoox.com
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace pinoox\portal;

use Illuminate\Database\Capsule\Manager as ObjectPortal2;
use Illuminate\Database\Schema\Builder as ObjectPortal1;
use pinoox\component\kernel\Exception;
use pinoox\component\source\Portal;
use Illuminate\Database\Connection;

/**
 * @method static ObjectPortal1 getSchema()
 * @method static ObjectPortal2 getCapsule()
 * @method static Connection run()
 * @method static \pinoox\component\database\DatabaseManager object()
 *
 * @see \pinoox\component\database\DatabaseManager
 */
class DatabaseManager extends Portal
{
    /**
     * @throws Exception
     */
    public static function __register(): void
    {
        self::__bind(\pinoox\component\database\DatabaseManager::class)->setArguments([self::getConfig()]);
    }

    /**
     * @throws Exception
     */
    public static function getConfig($key = null)
    {
        //get configs
        $mode = Config::name('~pinoox')->get('mode');
        if (!($config = Config::name('~database')->getLinear(null, $mode)))
            throw new Exception('Database config "' . $mode . '" not defined');

        return $config[$key] ?? $config;
    }

    /**
     * Get the registered name of the component.
     * @return string
     */
    public static function __name(): string
    {
        return 'database';
    }
}
