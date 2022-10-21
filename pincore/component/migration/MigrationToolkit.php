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

namespace pinoox\component\migration;

use pinoox\component\File;
use pinoox\storage\Database;

class MigrationToolkit
{

    private $schema = null;
    private $app_path = null;
    private $migration_path = null;
    private $package = null;
    private $namespace = null;
    private $errors = null;

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $db = new Database();
        $this->schema = $db->getSchema();
    }

    protected function createLogTable()
    {
        if ($this->schema->hasTable('migration_log')) return;
    }

    public function app_path($val)
    {
        $this->app_path = $val;
        return $this;
    }

    public function namespace($val)
    {
        $this->namespace = $val;
        return $this;
    }

    public function package($val)
    {
        $this->package = $val;
        return $this;
    }

    public function migration_path($val)
    {
        $this->migration_path = $val;
        return $this;
    }

    public function up()
    {
        if (!$this->checkPaths()) return $this;
        $migrations = File::get_files($this->migration_path);
        foreach ($migrations as $m) {
            $className = basename($m, '.php');
            $class = $this->namespace . $className;
            $obj = new $class();
            $obj->up();
        }

        return $this;
    }

    private function checkPaths()
    {
        if (empty($this->migration_path)) {
            $this->setError('migration path not defined');
            return false;
        }
        if (empty($this->namespace)) {
            $this->setError('namespace not defined');
            return false;
        }

        return true;

    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function isSuccess()
    {
        if (empty($this->getErrors())) return true;
    }

    private function setError($err)
    {
        $this->errors[] = $err;
    }

}