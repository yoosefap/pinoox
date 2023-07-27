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

use pinoox\component\migration\Migrator;
use pinoox\component\Terminal;
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

    protected function configure(): void
    {
        $this->addArgument('package', InputArgument::REQUIRED, 'Enter the package name that you want to migrate schemas');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $package = $input->getArgument('package');

        $migrator = new Migrator($package);

        try {
            $result = $migrator->run();
            $this->success($result);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        return Command::SUCCESS;
    }

    /*public function init($package)
    {
        try {
            $this->app = AppManager::getApp($package);
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

        $this->migrate();
    }*/

    /* private function migrate()
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

     }*/
}