<?php

namespace pinoox\storage;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\DatabaseManager;
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
class Database
{

    private Capsule $capsule;

    public function __construct()
    {
        $this->capsule = new Capsule;
        $config = Config::get('~database.development');
        $this->capsule->addConnection($config);
        
        // Set the event dispatcher used by Eloquent models... (optional)
        $this->capsule->setEventDispatcher(new Dispatcher(new Container()));
 
        //Make this Capsule instance available globally.
        $this->capsule->setAsGlobal();

        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $this->capsule->bootEloquent();
    }

    public function getSchema()
    {
        return $this->capsule->schema();
    }

    public function getCapsule()
    {
        return $this->capsule;
    }

}