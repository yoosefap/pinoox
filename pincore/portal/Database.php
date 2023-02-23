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

/**
 * @method static ObjectPortal1 getSchema()
 * @method static ObjectPortal2 getCapsule()
 * @method static \pinoox\component\database\Database object()
 *
 * @see \pinoox\component\database\Database
 */
class Database extends Portal
{
    /**
     * @throws Exception
     */
    public static function __register(): void
    {
        self::__bind(\pinoox\component\database\Database::class)->setArguments([self::getConfig()]);
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


    /**
     * Get exclude method names .
     * @return string[]
     */
    public static function __exclude(): array
    {
        return [];
    }


    /**
     * Get method names for callback object.
     * @return string[]
     */
    public static function __callback(): array
    {
        return [];
    }
}
