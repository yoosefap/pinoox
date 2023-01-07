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
        ['init', 'i', 'to run init', false],
    ];

    private $package;

    /**
     * @var MigrationConfig
     */
    private $mc = null;


    /**
     * @var boolean
     */
    private $isInit = true;

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
        $this->package = $this->argument('package');
        $this->chooseApp($this->package);//init cli

        $this->mc = new MigrationConfig($this->cli['path'], $this->cli['package']);
        $this->mc->load();

        if ($this->mc->getErrors())
            $this->error($this->mc->getLastError());

        $this->isInit = $this->option('i');

        $this->toolkit = (new MigrationToolkit())
            ->app_path($this->mc->app_path)
            ->migration_path($this->mc->migration_path)
            ->namespace($this->mc->namespace)
            ->package($this->mc->package)
            ->action($this->isInit ? 'init' : 'run')
            ->ready();
        
        if (!$this->toolkit->isSuccess()) {
            $this->error($this->toolkit->getErrors());
        }
        $this->schema = $this->toolkit->getSchema();
    }

    private function runUp()
    {
        $migrations = $this->toolkit->getMigrations();

        if (empty($migrations)) {
            $this->success('Nothing to migrate.');
            $this->newLine();
        }

        $batch = !$this->isInit && MigrationQuery::fetchLatestBatch($this->mc->package) ?? 0;

        foreach ($migrations as $m) {

            $start_time = microtime(true);
            $this->warning('Migrating: ');
            $this->info($m['fileName']);
            $this->newLine();
            $obj = new $m['classObject']();
            $obj->up();

            if (!$this->isInit) {
                MigrationQuery::insert($m['fileName'], $m['packageName'], $batch);
            }

            $end_time = microtime(true);
            $exec_time = $end_time - $start_time;

            //end migrating
            $this->success('Migrated: ');
            $this->info($m['fileName']);
            $this->gray(' (' . substr($exec_time, 0, 5) . 'ms)');
            $this->newLine();
        }
    }

}