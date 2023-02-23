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
use pinoox\component\helpers\Str;
use pinoox\component\package\App;
use pinoox\portal\Config;
use pinoox\portal\Database;

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
    private string $appPath;

    /**
     * path of migration files
     * @var string
     */
    private string $migrationPath;

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
    private string|array $errors = [];

    /**
     * migration files list
     * @var array
     */
    private array $migrations = [];

    public function __construct()
    {
        $this->schema = Database::getSchema();
        $this->cp = Database::getCapsule();
    }

    public function appPath($val): self
    {
        $this->appPath = $val;
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

    public function migrationPath($val): self
    {
        $this->migrationPath = $val;
        return $this;
    }

    private function getFromDB(): ?array
    {
        $batch = $this->action == 'rollback' ?
            MigrationQuery::fetchLatestBatch($this->package) : null;

        return MigrationQuery::fetchAllByBatch($batch, $this->package);
    }

    private function isExistsMigrationTable(): bool
    {
        $isExists = $this->schema->hasTable('migration');
        if (!$isExists) {
            $this->setError('Migration table not exists. First of all init migration table');
            return false;
        }
        return true;
    }

    public function ready(): self
    {
        if (!$this->checkPaths()) return $this;

        $migrations = $this->readyFromPath();

        if ($this->action != 'init' && $this->isExistsMigrationTable()) {
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
        $files = File::get_files($this->migrationPath);
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
        $isLoad = $this->loadMigrationClass($this->migrationPath . $fileName . '.php');
        $classObject = $this->namespace . $className;

        return [$fileName, $className, $classObject, $isLoad];
    }

    private function build($sync, $className, $fileName, $classObject, $isLoad): array
    {
        try {
            $corePrefix = Database::getConfig('prefix');
            $prefix = App::get('db.prefix') ?? '';
            $prefix = $corePrefix . $prefix;
        } catch (\Exception $e) {
            $prefix = '';
        }

        return [
            'sync' => $sync,
            'isLoad' => $isLoad,
            'packageName' => $this->package,
            'className' => $className,
            'fileName' => $fileName,
            'classObject' => $classObject,
            'dbPrefix' => $prefix,
        ];
    }

    public function getMigrations(): array
    {
        return $this->migrations;
    }

    public function getSchema(): \Illuminate\Database\Schema\Builder
    {
        return $this->schema;
    }

    private function convertToTimestampPrefix($fileName)
    {
        $timestamp = date('Ymdhis');
        $newFileName = $timestamp . '_' . $fileName;
        rename($this->migrationPath . $fileName . '.php', $this->migrationPath . $newFileName . '.php');
        return $fileName;
    }

    private function getClassName($fileName): string
    {
        $justFilename = substr($fileName, 15);
        if (!$justFilename) {
            $justFilename = $this->convertToTimestampPrefix($fileName);
        }
        return Str::toCamelCase($justFilename);
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
        if (empty($this->migrationPath)) {
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