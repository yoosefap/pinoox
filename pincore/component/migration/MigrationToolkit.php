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

use Illuminate\Database\Capsule\Manager;
use pinoox\component\File;
use pinoox\component\HelperString;
use pinoox\storage\Database;

class MigrationToolkit
{

    private $schema = null;
    /**
     * @var Manager;
     */
    private $cp = null;
    private $app_path = null;
    private $migration_path = null;
    private $package = null;
    private $namespace = null;
    private $errors = null;
    private $migrations = [];

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $db = new Database();
        $this->schema = $db->getSchema();
        $this->cp = $db->getCapsule();
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

    public function ready()
    {
        if (!$this->checkPaths()) return $this;
        $migrations = File::get_files($this->migration_path);

        foreach ($migrations as $f) {
            $fileName = $this->getFileName($f);
            $className = $this->getClassName($fileName);
            $this->loadMigrationClass($this->migration_path . $fileName . '.php');

            $classObject = $this->namespace . $className;
            $this->migrations[] = [
                'packageName' => $this->package,
                'className' => $className,
                'fileName' => $fileName,
                'tableName' => HelperString::toUnderScore($className),
                'classObject' => $classObject,
            ];
        }
        return $this;
    }

    public function getMigrations()
    {
        return $this->migrations;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    private function convertToTimestampPrefix($fileName)
    {
        $timestamp = date('Ymdhis');
        $newFileName = $timestamp . '_' . $fileName;
        rename($this->migration_path . $fileName . '.php', $this->migration_path . $newFileName . '.php');
        return $fileName;
    }

    private function getClassName($fileName)
    {
        $justFilename = substr($fileName, 15);
        if (!$justFilename) {
            $justFilename = $this->convertToTimestampPrefix($fileName);
        }
        return HelperString::toCamelCase($justFilename);
    }

    private function getFileName($file)
    {
        return basename($file, '.php');
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

    private function loadMigrationClass($classFile)
    {
        spl_autoload_register(function ($className) use ($classFile) {
            include_once $classFile;
        });
    }

    public function saveLog($migrations){

    }
}