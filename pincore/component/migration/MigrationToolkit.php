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
use pinoox\component\kernel\Exception;
use pinoox\portal\DB;
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
        $this->schema = DB::getSchema();
        $this->cp = DB::getCapsule();
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
     * actions: init, run, rollback, create
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
        if (empty($migrations)) return $this;

        if ($this->action != 'create' && $this->action != 'init' && $this->isExistsMigrationTable()) {
            $migrations = $this->syncWithDB($migrations);
        }

        if (!empty($migrations)) {
            foreach ($migrations as $m) {
                list($fileName, $migrationFile) = $this->extract($m);

                if ($this->action === 'rollback' && empty($m['sync'])) continue;
                if ($this->action === 'run' && !empty($m['sync'])) continue;

                try {
                    $this->migrations[] = [
                        'sync' => $m['sync'],
                        'packageName' => $this->package,
                        'migrationFile' => $migrationFile,
                        'fileName' => $fileName,
                    ];
                } catch (Exception $e) {
                    $this->setError($e);
                }
            }

        }

        return $this;
    }

    private function readyFromPath(): array
    {
        if (!file_exists($this->migrationPath)) {
            mkdir($this->migrationPath, 0755, true);
        }

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
        $fileName = $this->getFileName($item);
        $migrationFile = $this->migrationPath . DS . $fileName . '.php';

        return [$fileName, $migrationFile];
    }


    public function getMigrations(): array
    {
        return $this->migrations;
    }

    public function generateMigrationFileName($modelName): string
    {
        // Get the current timestamp in the required format
        $timestamp = date('Y_m_d_His');

        // Convert the model name to snake_case and add "create_" prefix
        $tableName = 'create_' . $this->snakeCase($modelName) . '_table';

        // Combine the timestamp and table name to form the migration file name
        return $timestamp . '_' . $tableName . '.php';
    }

    private function snakeCase($string): string
    {
        // Replace spaces and underscores with dashes
        $string = str_replace([' ', '_'], '-', $string);

        // Convert the string to lowercase
        return strtolower($string);
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

        require_once $classFile;

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