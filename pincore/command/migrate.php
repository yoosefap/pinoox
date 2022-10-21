<?php

namespace pinoox\command;


use pinoox\component\console;
use pinoox\component\interfaces\CommandInterface;
use pinoox\component\migration\MigrationConfig;
use pinoox\component\migration\MigrationToolkit;


class migrate extends console implements CommandInterface
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
    protected $description = "Migrate Database Schemas";

    /**
     * The console command Arguments.
     *
     * @var array
     */
    protected $arguments = [
        ['package', false, 'name of package that you want to migrate database.', null],
    ];

    /**
     * The console command Options.
     *
     * @var array
     */
    protected $options = [
    ];

    private $config =
        null;

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->init();
        $this->execute_up();
    }


    private function init()
    {
        list($this->app_path, $this->package) = $this->chooseApp();
        $this->config = new MigrationConfig($this->app_path, $this->package);

        if ($this->config->getErrors()) {
            $this->error($this->config->getLastError());
        }
    }

    private function execute_up()
    {
        $toolkit = (new MigrationToolkit())
            ->app_path($this->app_path)
            ->migration_path($this->migration_path)
            ->namespace($this->namespace)
            ->package($this->package)
            ->up();

        if ($toolkit->isSuccess()) {
            $this->newLine();
            $this->success('migration done!');
        }
    }


    /*private function generate_config($path, $db_dev)
    {
        $file = $this->app_path . self::DS . 'pinker' . self::DS . 'migration.php';
        if (file_exists($file)) return $file;
        $data = [
            'paths' => [
                'migrations' => $path
            ],
            'migration_base_class' => 'pinoox' . self::DS . 'app' . self::DS . $this->package . self::DS . 'migration',
            'environments' => [
                'default_migration_table' => 'migration_log',
                'default_database' => 'dev',
                'dev' => [
                    'adapter' => $db_dev['driver'],
                    'host' => $db_dev['host'],
                    'name' => $db_dev['database'],
                    'user' => $db_dev['username'],
                    'pass' => $db_dev['password'],
                ]
            ]
        ];

        $data_for_save = '<?' . 'php' . "\n";
        $data_for_save .= '//pinoox config file, generated at "' . gmdate('Y-m-d H:i') . "\"\n\n";
        $data_for_save .= 'return ' . var_export($data, true) . ";\n\n//end of config";

        File::generate($file, $data_for_save);
        return $file;
    }*/

}