<?php

namespace pinoox\command;


use pinoox\component\console;
use pinoox\component\interfaces\CommandInterface;
use pinoox\component\migration\MigrationConfig;
use pinoox\component\migration\MigrationToolkit;


class migrationUp extends console implements CommandInterface
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "db:migrate";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Migrate the database migrations";

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
        $this->run_up();
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

    private function run_up()
    {
        $migrations = $this->toolkit->getMigrations();

        if (empty($migrations)) {
            $this->error('There are no migrations!');
            $this->newLine();
        }
        $this->success('start migrating...');
        $this->newLine();
        foreach ($migrations as $m) {
            $tableName = $m['packageName'] . '_' . $m['tableName'];

            //check if already exists
            if ($this->schema->hasTable($tableName)) {
                $this->warning('Table: "' . $tableName . '" already exists');
                $this->gray('  File: "' . $m['fileName'] . '.php"');
                $this->newLine();
                continue;
            }

            $this->success('Table: "' . $tableName . '" migrated');
            $this->gray('  File: "' . $m['fileName'] . '.php"');
            $this->newLine();
            $obj = new $m['classObject']();
            $obj->prefix = $m['packageName'] . '_';
            $obj->up();
        }

        if ($this->toolkit->isSuccess()) {
            $this->newLine();
            $this->success('migration done!');
        }
    }

}