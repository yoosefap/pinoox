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

use Doctrine\DBAL\Schema\Schema;
use Illuminate\Database\Capsule\Manager;
use pinoox\component\File;
use pinoox\component\HelperString;
use pinoox\component\database\Database;
use pinoox\model\MigrationModel;

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
     * actions: rollback,run,init,status
     * @var string
     */
    private string $action = 'run';

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
    public function action($action): self
    {
        $this->action = $action;
        return $this;
    }

    public function migration_path($val): self
    {
        $this->migration_path = $val;
        return $this;
    }

    private function getFromDB(): mixed
    {
        $batch = $this->action == 'rollback' ?
            MigrationQuery::fetchLatestBatch($this->package) : null;

        return MigrationQuery::fetchAllByBatch($batch, $this->package);
    }

    private function isExistsMigrationTable()
    {
        $isExists = Database::establish()->getSchema()->hasTable('migration');
        if (!$isExists) {
            $this->setError('Migration table not exists.');
            return false;
        }
        return true;
    }

    public function ready(): self
    {
        if (!$this->checkPaths()) return $this;

        $migrations = $this->readyFromPath();

        if ($this->action !='init' && $this->isExistsMigrationTable()) {
            $migrations = $this->syncWithDB($migrations);
        }

        if (!empty($migrations)) {
            foreach ($migrations as $m) {
                list($fileName, $className, $classObject, $isLoad) = $this->extract($m);

                if ($this->action === 'rollback' && empty($m['sync'])) continue;
                if ($this->action === 'run' && !empty($m['sync'])) continue;

                $this->migrations[] = $this->build($m['sync'], $className, $fileName, $classObject, $isLoad);
            }
        }

        return $this;
    }

    private function readyFromPath(): array
    {
        $files = File::get_files($this->migration_path);
        return array_map(function ($f) {
            return [
                'sync' => false,
                'path' => $f,
                'migration' => basename($f, '.php'),
            ];
        }, $files);
    }

    private function extract($item): array
    {
        $fileName = $this->getFileName($item);
        $className = $this->getClassName($fileName);
        $isLoad = $this->loadMigrationClass($this->migration_path . $fileName . '.php');
        $classObject = $this->namespace . $className;

        return [$fileName, $className, $classObject, $isLoad];
    }


    private function build($sync, $className, $fileName, $classObject, $isLoad): array
    {
        return [
            'sync' => $sync,
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
        if (is_array($file)) {
            return $file['migration'];
        }
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
        if (!file_exists($classFile)) return false;

        spl_autoload_register(function ($className) use ($classFile) {
            include_once $classFile;
        });
        return true;
    }

    private function syncWithDB($migrations): array
    {
        if (empty($migrations)) return [];

        $records = $this->getFromDB();

        //find migrations in database
        return array_map(function ($m) use ($records) {
            $index = array_search($m['migration'], array_column($records, 'migration'));

            if ($index !== false) {
                $m['sync'] = $records[$index] ?? null;
            }
            return $m;
        }, $migrations);
    }
}