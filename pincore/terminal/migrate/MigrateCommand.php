<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace pinoox\terminal\migrate;

use pinoox\component\kernel\Exception;
use pinoox\component\migration\MigrationQuery;
use pinoox\component\Terminal;
use pinoox\portal\AppManager;
use pinoox\portal\MigrationToolkit;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


// the "name" and "description" arguments of AsCommand replace the
// static $defaultName and $defaultDescription properties
#[AsCommand(
    name: 'migrate',
    description: 'Migrate schemas.',
)]
class MigrateCommand extends Terminal
{
    private string $package;

    private array $app;

    /**
     * @var MigrationToolkit
     */
    private $toolkit = null;

    protected function configure(): void
    {
        $this->addArgument('package', InputArgument::REQUIRED, 'Enter the package name of app you want to migrate schemas');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->package = $input->getArgument('package');

        $this->init();
        $this->migrate();

        return Command::SUCCESS;
    }

    private function init()
    {
        try {
            $this->app = AppManager::getApp($this->package);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->toolkit = MigrationToolkit::appPath($this->app['path'])
            ->migrationPath($this->app['migration'])
            ->package($this->app['package'])
            ->namespace($this->app['namespace'])
            ->load();

        if (!$this->toolkit->isSuccess()) {
            $this->error($this->toolkit->getErrors());
        }
    }

    private function migrate()
    {
        $migrations = $this->toolkit->getMigrations();

        if (empty($migrations)) {
            $this->success('Nothing to migrate.');
        }

        $batch = MigrationQuery::fetchLatestBatch($this->app['package']) ?? 0;

        foreach ($migrations as $m) {
            $start_time = microtime(true);
            $this->warning('Migrating: ');
            $this->warning($m['fileName']);
            $this->newline();

            $obj = new $m['classObject']();
            $obj->up();

            MigrationQuery::insert($m['fileName'], $m['packageName'], $batch);

            $end_time = microtime(true);
            $exec_time = $end_time - $start_time;

            //end migrating
            $this->success('Migrated: ' . $m['fileName']);
            $this->info(' (' . substr($exec_time, 0, 5) . 'ms)');
            $this->newline();
        }

    }
}