<?php

namespace pinoox\command;


use pinoox\component\console;
use pinoox\component\interfaces\CommandInterface;
use pinoox\component\migration\MigrationConfig;
use pinoox\component\migration\MigrationToolkit;


class migrationDown extends console implements CommandInterface
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "db:rollback";

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
        $this->reverse_down();
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

    private function reverse_down()
    {
        $this->success('start rolling back...');
        $this->newLine();
        $migrations = $this->toolkit->getMigrations();
        foreach ($migrations as $m) {

            $this->success('Table: "' . $m['tableName'] . '" rollback');
            $this->gray('  File: "' . $m['fileName'] . '"');
            $this->newLine();
            $obj = new $m['classObject']();
            $obj->down();
        }

        if ($this->toolkit->isSuccess()) {
            $this->newLine();
            $this->success('rollback done!');
        }
    }

}