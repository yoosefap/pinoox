<?php

namespace pinoox\command;


use pinoox\app\com_pinoox_manager\model\AppModel;
use pinoox\component\Config;
use pinoox\component\console;
use pinoox\component\Dir;
use pinoox\component\File;
use pinoox\component\interfaces\CommandInterface;


class createMigrate extends console implements CommandInterface
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "db:create";

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
        ['package', false, 'name of package that you want to migrate database.', null],
    ];

    /**
     * The console command Options.
     *
     * @var array
     */
    protected $options = [
    ];

    private $package = null;
    private $app_path = null;
    private $sub_path = null;
    private $config = null;
    private $migration_path = null;
    private $namespace = null;

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
        $this->sub_path = self::DS . 'database' . self::DS . 'migrations' . self::DS;
        $this->migration_path = $this->app_path . $this->sub_path;

        // set namespace
        $this->namespace = 'pinoox' . self::DS . 'app' . self::DS . $this->package . $this->sub_path;

        $this->config = $this->getConfig();
    }

    private function execute_up()
    {
        $migrations = File::get_files($this->migration_path);
        foreach ($migrations as $m) {
            $className = basename($m, '.php');
            $class = $this->namespace . $className;
            $obj = new $class();
            $obj->up();
        }
    }

    private function getConfig()
    {
        $database = Config::get('~database');
        if (empty($database)) $this->error('database config not exists!');
        if (empty($database['development'])) $this->error('development params of database config is not set!');

        return $database;
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