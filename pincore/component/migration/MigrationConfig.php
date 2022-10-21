<?php

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

namespace pinoox\component\migration;

class MigrationConfig
{
    private $errors = null;
    public $app_path = null;
    public $sub_path = null;
    public $migration_path = null;
    public $namespace = null;
    public $package = null;
    private $config = null;
    const DS = DIRECTORY_SEPARATOR;

    public function __construct($app_path, $package)
    {
        $this->app_path = $app_path;
        $this->package = $package;
    }

    public function getErrors()
    {
        return $this->errors;
    }
    public function getLastError()
    {
        return end($this->errors);
    }

    public function hasError()
    {
        return !empty($this->errors);
    }

    private function setError($err)
    {
        $this->errors[] = $err;
    }


    public function load()
    {
        $this->sub_path = self::DS . 'database' . self::DS . 'migrations' . self::DS;
        $this->migration_path = $this->app_path . $this->sub_path;

        // set namespace
        $this->namespace = 'pinoox' . self::DS . 'app' . self::DS . $this->package . $this->sub_path;

        $this->config = $this->getConfig();
    }

    private function getConfig()
    {
        $database = Config::get('~database');
        if (empty($database)) {
            $this->setError('database config not exists!');
            return false;
        }
        if (empty($database['development'])) {
            $this->setError('development params of database config is not set!');
            return false;
        }

        return $database;
    }
}