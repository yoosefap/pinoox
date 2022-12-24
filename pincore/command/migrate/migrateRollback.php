<?php

namespace pinoox\command\migrate;


use pinoox\component\console;
use pinoox\component\interfaces\CommandInterface;
use pinoox\component\migration\MigrationConfig;
use pinoox\component\migration\MigrationQuery;
use pinoox\component\migration\MigrationToolkit;


class migrateRollback extends console implements CommandInterface
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "migrate:rollback";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Rollback the database migrations";

    /**
     * The console command Arguments.
     *
     * @var array
     */
    protected $arguments = [
        ['package', false, 'package name of app that you want to migrate.', null],
    ];

    /**
     * The console command Options.
     *
     * @var array
     */
    protected $options = [
    ];

    /**
     * @var MigrationConfig
     */
    private $mc = null;

    /**
     * @var MigrationToolkit
     */
    private $toolkit = null;

    /**
     * @var MigrationToolkit
     */
    private $schema = null;

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->init();
        $this->reverseDown();
    }

    private function init()
    {
        $this->chooseApp($this->argument('package'));//init cli

        $this->mc = new MigrationConfig($this->cli['path'], $this->cli['package']);
        $this->mc->load();

        if ($this->mc->getErrors())
            $this->error($this->mc->getLastError());

        $this->toolkit = (new MigrationToolkit())
            ->app_path($this->mc->app_path)
            ->migration_path($this->mc->migration_path)
            ->namespace($this->mc->namespace)
            ->package($this->mc->package)
            ->action('rollback')
            ->ready();
        
        $this->schema = $this->toolkit->getSchema();
    }

    private function reverseDown()
    {
        $migrations = $this->toolkit->getMigrations();

        if (empty($migrations)) {
            $this->success('Nothing to rollback.');
            $this->newLine();
        }

        $batch = MigrationQuery::fetchLatestBatch($this->mc->package);

        foreach ($migrations as $m) {

            if (!$m['isLoad']) {
                $this->danger('Migration not found: ');
                $this->info($m['fileName']);
                $this->newLine();
                continue;
            }

            $start_time = microtime(true);
            $this->warning('Rolling back: ');
            $this->info($m['fileName']);
            $this->newLine();
            $obj = new $m['classObject']();
            $obj->down();

            MigrationQuery::delete($batch, $m['packageName']);

            $end_time = microtime(true);
            $exec_time = $end_time - $start_time;

            //end migrating
            $this->success('Rolled back: ');
            $this->info($m['fileName']);
            $this->gray(' (' . substr($exec_time, 0, 5) . 'ms)');
            $this->newLine();
        }

    }

}