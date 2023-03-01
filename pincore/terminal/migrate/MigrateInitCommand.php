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
use pinoox\component\package\Package;
use pinoox\component\Terminal;
use pinoox\portal\AppManager;
use pinoox\portal\MigrationToolkit;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateInitCommand extends Terminal
{

    protected static $defaultName = 'migrate:init';

    protected static $defaultDescription = 'Initialize migration repository and create tables';


    /**
     * @var MigrationToolkit
     */
    private $toolkit = null;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->init();
        $this->migrate();

        return Command::SUCCESS;
    }


    private function init()
    {
        $pincore = AppManager::getApp(Package::pincore);
dd($pincore);
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
            $this->success('Migrating: ');
            $this->success($m['fileName']);
            $this->newline();

            $obj = new $m['classObject']();
            $obj->prefix = $m['dbPrefix'];
            $obj->up();

            MigrationQuery::insert($m['fileName'], $m['packageName'], $batch);

            $end_time = microtime(true);
            $exec_time = $end_time - $start_time;

            //end migrating
            $this->success('Migrated: ' . $m['fileName']);
            $this->info(' (' . substr($exec_time, 0, 5) . 'ms)');
        }
    }
}