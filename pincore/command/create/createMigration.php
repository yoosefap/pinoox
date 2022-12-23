<?php

namespace pinoox\command\create;

use pinoox\component\ClassBuilder;
use pinoox\component\console;
use pinoox\component\File;
use pinoox\component\HelperString;
use pinoox\component\interfaces\CommandInterface;
use pinoox\component\migration\MigrationConfig;
use pinoox\component\migration\MigrationToolkit;


class createMigration extends console implements CommandInterface
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "create:migration";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create a new Migration Schema";

    /**
     * The console command Arguments.
     *
     * @var array
     */
    protected $arguments = [
        ['className', true, 'the name of migration class name', null],
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
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->init();
        $this->create();
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

    }

    private function create()
    {
        //get input
        $arg = $this->argument('className');
        $className = HelperString::toCamelCase($arg);
        $fileName = HelperString::toUnderScore($className);

        //check availability
        $files = File::get_files($this->mc->migration_path);

        foreach ($files as $f) {
            $name = pathinfo($f, PATHINFO_FILENAME);
            //eliminate timestamp
            $name_no_timestamp = substr($name, 15);
            if ($name_no_timestamp == $fileName) {
                $this->error('☓  The migration class name "' . $className . '" already exists ');
            }
        }

        //create timestamp filename
        $exportFile = date('Ymdhis') . '_' . $fileName . '.php';

        $exportPath = $this->mc->migration_path . $exportFile;

        try {
            $builder = ClassBuilder::init($className)
                ->namespace("pinoox\\app\\" . $this->mc->package . "\\database\\migrations")
                ->use('Illuminate\\Database\\Schema\\Blueprint')
                ->use('pinoox\component\migration\MigrationBase')
                ->extends('MigrationBase')
                ->method('public function up', 'Run the migrations.')
                ->method('public function down', 'Reverse the migrations.')
                ->build()
                ->export($exportPath);
            if ($builder->isSuccess()) {
                //print success messages
                $this->success('✓ Created Class ' . $className);
                $this->gray(' in path: ' . $this->mc->folders);
                $this->warning($exportFile);
                $this->newLine();
            } else {
                $this->error('Can\'t generate a new migration class!');
            }
        } catch (\Exception $e) {
            $this->error($e);
        }

    }


}