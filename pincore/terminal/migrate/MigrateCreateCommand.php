<?php

namespace pinoox\terminal\migrate;

use pinoox\component\helpers\PhpFile\MigrationFile;
use pinoox\component\helpers\Str;
use pinoox\component\Terminal;
use pinoox\portal\AppManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use pinoox\portal\MigrationToolkit;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand(
    name: 'migrate:create',
    description: 'Create a new Migration Schema.',
)]
class MigrateCreateCommand extends Terminal
{
    private string $package;

    private array $app;

    private string $className;


    /**
     * @var MigrationToolkit
     */
    private $toolkit = null;

    protected function configure(): void
    {
        $this
            ->addArgument('className', InputArgument::REQUIRED, 'Enter name of migration class name')
            ->addArgument('package', InputArgument::REQUIRED, 'Enter the package name of app you want to migrate schemas');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->package = $input->getArgument('package');
        $this->className = $input->getArgument('className');

        $this->init();
        $this->create();

        return Command::SUCCESS;
    }


    private function init()
    {
        try {
            $this->app = AppManager::getApp($this->package);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->toolkit = MigrationToolkit::appPath($this->app['path'])
            ->migrationPath($this->app['migration'])
            ->package($this->app['package'])
            ->namespace($this->app['namespace'])
            ->load();

        if (!$this->toolkit->isSuccess()) {
            $this->error($this->toolkit->getErrors());
        }
    }

    private function create()
    {
        //get input
        $this->className = Str::toCamelCase($this->className);
        $fileName = Str::toUnderScore($this->className);

        //check availability
        $finder = new Finder();
        $finder->in($this->app['migration'])
            ->files()
            ->filter(static function (SplFileInfo $file) {
                return $file->isDir() || \preg_match('/\.(php)$/', $file->getPathname());
            });


        foreach ($finder as $f) {
            $name = $f->getBasename('.php');

            //eliminate timestamp
            $name_no_timestamp = substr($name, 15);
            if ($name_no_timestamp == $fileName) {
                $this->error('☓  The migration class name "' . $this->className . '" already exists ');
            }
        }

        //create timestamp filename
        $exportFile = date('Ymdhis') . '_' . $fileName . '.php';
        $exportPath = $this->app['migration'] . DS . $exportFile;

        try {
            $isCreated = MigrationFile::create(
                exportPath: $exportPath,
                className: $this->className,
                package: $this->app['package'],
                namespace: $this->app['namespace'] . DS . $this->app['migration_relative_path']
            );

            if ($isCreated) {
                //print success messages
                $this->success('✓ Created Class ' . $this->className);
                $this->success(' in path: ' . $this->app['migration']);
                $this->warning(DS . $exportFile);
                $this->newLine();
            } else {
                $this->error('Can\'t generate a new migration class!');
            }
        } catch (\Exception $e) {
            $this->error($e);
        }

    }


}