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
use pinoox\component\database\Database;

class MigrationToolkit
{

    private $schema = null;
    /**
     * @var Manager;
     */
    private Manager $cp;
    /**
     * path of app
     * @var string
     */
    private string $app_path;

    /**
     * path of migration files
     * @var string
     */
    private string $migration_path;

    /**
     * package name of app
     * @var string
     */
    private string $package;

    /**
     * namespace
     * @var string
     */
    private string $namespace;

    /**
     * use filter and exclude migrations in database, for example when run commands like: rollback OR status
     * @var bool
     */
    private bool $fromDB = true;
    private bool $checkDB = true;

    /**
     * errors
     * @var array | string
     */
    private $errors = [];

    /**
     * migration files list
     * @var array
     */
    private array $migrations = [];

    public function __construct()
    {
        $this->init();
    }

    public function init(): void
    {
        $db = Database::establish();
        $this->schema = $db->getSchema();
        $this->cp = $db->getCapsule();
    }

    public function app_path($val): self
    {
        $this->app_path = $val;
        return $this;
    }

    public function namespace($val): self
    {
        $this->namespace = $val;
        return $this;
    }

    public function package($val): self
    {
        $this->package = $val;
        return $this;
    }


    /**
     * @return $this
     */
    public function fromDB($fromDB = true, $checkDB = false): self
    {
        $this->fromDB = $fromDB;
        $this->checkDB = $checkDB;
        return $this;
    }

    public function migration_path($val): self
    {
        $this->migration_path = $val;
        return $this;
    }

    private function getFromDB(): array
    {
        $batch = MigrationQuery::fetchLatestBatch($this->package);
        $migrations = MigrationQuery::fetchAllByBatch($batch, $this->package);
        return array_map(function ($m) {
            return $m['migration'];
        }, $migrations);
    }

    public function ready(): self
    {
        if (!$this->checkPaths()) return $this;

        if ($this->fromDB) {
            $migrations = $this->getFromDB();
        } else {
            $migrations = $this->readyFromPath();
        }

        foreach ($migrations as $m) {
            list($fileName, $className, $classObject, $isLoad) = $this->extract($m);
            if ($this->checkDB && MigrationQuery::is_exists($fileName, $this->package)) continue;

            $this->migrations[] = $this->build($className, $fileName, $classObject, $isLoad);
        }

        return $this;
    }

    private function readyFromPath(): array
    {
        return File::get_files($this->migration_path);
    }

    private function extract($item): array
    {
        $fileName = $this->getFileName($item);
        $className = $this->getClassName($fileName);
        $isLoad = $this->loadMigrationClass($this->migration_path . $fileName . '.php');
        $classObject = $this->namespace . $className;

        return [$fileName, $className, $classObject, $isLoad];
    }


    private function build($className, $fileName, $classObject, $isLoad): array
    {
        return [
            'isLoad' => $isLoad,
            'packageName' => $this->package,
            'className' => $className,
            'fileName' => $fileName,
            'classObject' => $classObject,
        ];
    }

    public function getMigrations(): array
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

    private function getClassName($fileName): string
    {
        $justFilename = substr($fileName, 15);
        if (!$justFilename) {
            $justFilename = $this->convertToTimestampPrefix($fileName);
        }
        return HelperString::toCamelCase($justFilename);
    }

    private function getFileName($file): string
    {
        return basename($file, '.php');
    }

    private function checkPaths(): bool
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

    public function getErrors($end = true)
    {
        if ($end) return end($this->errors);
        return $this->errors;
    }

    public function isSuccess(): bool
    {
        if (empty($this->getErrors()))
            return true;
        return false;
    }

    private function setError($err): void
    {
        $this->errors[] = $err;
    }


    private function loadMigrationClass($classFile): bool
    {
        if (!file_exists($classFile))
            return false;
        spl_autoload_register(function ($className) use ($classFile) {
            include_once $classFile;
        });
        return true;
    }

}