<?php

namespace pinoox\command\migrate;


use pinoox\component\console;
use pinoox\component\interfaces\CommandInterface;
use pinoox\component\migration\MigrationConfig;
use pinoox\component\migration\MigrationQuery;
use pinoox\component\migration\MigrationToolkit;


class migrateRun extends console implements CommandInterface
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "migrate";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Migrate schemas";

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
        $this->runUp();
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
            ->ready();

        $this->schema = $this->toolkit->getSchema();
    }

    private function runUp()
    {
        $migrations = $this->toolkit->getMigrations();

        if (empty($migrations)) {
            $this->success('Nothing to migrate.');
            $this->newLine();
        }

        $batch = MigrationQuery::fetchLatestBatch($this->mc->package);

        foreach ($migrations as $m) {

            $start_time = microtime(true);
            $this->warning('Migrating: ');
            $this->info( $m['fileName'] );
            $this->newLine();
            $obj = new $m['classObject']();
            $obj->up();

            MigrationQuery::insert($m['fileName'], $m['packageName'], $batch);

            $end_time = microtime(true);
            $exec_time = $end_time - $start_time;

            //end migrating
            $this->success('Migrated: ');
            $this->info($m['fileName']);
            $this->gray(' ('.substr($exec_time, 0,5).'ms)');
            $this->newLine();
        }
    }

}