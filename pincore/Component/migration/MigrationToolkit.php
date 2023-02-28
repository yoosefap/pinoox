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
use pinoox\component\helpers\Str;
use pinoox\component\kernel\Exception;
use pinoox\portal\Database;
use Symfony\Component\Finder\Finder;

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
     * namespace of app
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

    public function package($val): self
    {
        $this->package = $val;
        return $this;
    }

    public function namespace($val): self
    {
        $this->namespace = $val;
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
        $isExists = $this->schema->hasTable('pincore_migration');
        if (!$isExists) {
            $this->setError('Migration table not exists. First of all init migration table');
            return false;
        }
        return true;
    }

    public function load(): self
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

                try {
                    $this->migrations[] = $this->build($m['sync'], $className, $fileName, $classObject, $isLoad);
                } catch (Exception $e) {
                    $this->setError($e);
                }
            }
        }

        return $this;
    }

    private function readyFromPath(): array
    {
        $files = [];
        $finder = new Finder();
        $finder->in($this->migrationPath)->files();
        foreach ($finder as $f) {
            $files[] = [
                'sync' => false,
                'path' => $f->getRealPath(),
                'migration' => $f->getBasename('.php'),
            ];
        }
        return $files;
    }

    private function extract($item): array
    {
        $namespace = $this->namespace . str_replace([PINOOX_APP_PATH, $this->package, $item['migration'] . '.php'], '', $item['path']);
        $fileName = $this->getFileName($item);
        $className = $this->getClassName($fileName);
        $isLoad = $this->loadMigrationClass($this->migrationPath . DS . $fileName . '.php');
        $classObject = $namespace . $className;

        return [$fileName, $className, $classObject, $isLoad];
    }

    /**
     * @throws Exception
     */
    private function build($sync, $className, $fileName, $classObject, $isLoad): array
    {
        return [
            'sync' => $sync,
            'isLoad' => $isLoad,
            'packageName' => $this->package,
            'className' => $className,
            'fileName' => $fileName,
            'classObject' => $classObject,
            'dbPrefix' => Database::getConfig('prefix') . ($this->package != 'pincore' ? $this->package . '_' : ''),
        ];
    }

    public function getMigrations(): array
    {
        return $this->migrations;
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