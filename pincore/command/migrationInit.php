<?php

namespace pinoox\command;


use pinoox\component\console;
use pinoox\component\interfaces\CommandInterface;
use pinoox\component\migration\MigrationConfig;
use pinoox\component\migration\MigrationToolkit;


class migrationInit extends console implements CommandInterface
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "db:init";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Initialize migration tables and configs";

    /**
     * The console command Arguments.
     *
     * @var array
     */
    protected $arguments = [
    ];

    /**
     * The console command Options.
     *
     * @var array
     */
    protected $options = [
    ];

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->success('Initialized migration table');
        $this->newLine();
        $this->execute('db:migrate', ['pincore']);
    }

}