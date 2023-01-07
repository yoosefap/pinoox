<?php

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

use pinoox\component\Config;
use pinoox\component\database\Database;

class MigrationConfig
{
    private $errors = null;
    public $app_path = null;
    public $folders = null;
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
        $this->folders = self::DS . 'database' . self::DS . 'migrations' . self::DS;
        $this->migration_path = $this->app_path . $this->folders;

        //namespace
        if ($this->package == 'pincore') {
            $this->namespace = 'pinoox' . $this->folders;
        } else {
            $this->namespace = 'pinoox' . self::DS . 'app' . self::DS . $this->package . $this->folders;
        }

        //check database
        $this->isPrepareDB();

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

    public function isPrepareDB(): bool
    {
        $db = Database::establish();
        if (empty($db->getCapsule()->getConnection())) {
            $this->setError('Database not connected');
            return false;
        }
        return true;
    }
}