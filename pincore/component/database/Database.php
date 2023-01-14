<?php

namespace pinoox\component\database;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use pinoox\component\Config;

/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       pinoox.com
 * @copyright  pinoox
 */

use \Illuminate\Database\Schema\Builder;
use Symfony\Component\VarDumper\Cloner\Data;

class Database
{
    private static $db;
    private Capsule $capsule;

    public static function establish(): Database
    {
        if (empty(self::$db)) {
            self::$db = new Database();
        }
        return self::$db;
    }

    public function __construct()
    {
        $config = Config::get('~database.development');

        $this->capsule = new Capsule;

        $this->capsule->addConnection($config);

        // Set the event dispatcher used by Eloquent models... (optional)
        $this->capsule->setEventDispatcher(new Dispatcher(new Container()));

        //Make this Capsule instance available globally.
        $this->capsule->setAsGlobal();

        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $this->capsule->bootEloquent();
    }

    public function getSchema(): Builder
    {
        return $this->capsule->schema();
    }

    public function getCapsule(): Capsule
    {
        return $this->capsule;
    }

}