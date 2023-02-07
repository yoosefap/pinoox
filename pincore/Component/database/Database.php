<?php

namespace pinoox\component\database;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

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
use pinoox\portal\Config;
use Symfony\Component\VarDumper\Cloner\Data;

class Database
{
    private Capsule $capsule;

    public function __construct(array $config)
    {
        $this->capsule = new Capsule;

        $this->capsule->addConnection($config);

        // Set the event dispatcher used by Eloquent models... (optional)
        //$this->capsule->setEventDispatcher(new Dispatcher(new Container()));

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