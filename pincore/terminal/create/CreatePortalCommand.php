<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */


namespace pinoox\terminal\create;

use pinoox\component\helpers\PhpFile\PortalFile;
use pinoox\component\helpers\Str;
use pinoox\component\Terminal;
use pinoox\portal\AppManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'create:portal',
    description: 'Create a new Portal class.',
)]
class CreatePortalCommand extends Terminal
{
    private string $portalName;
    private string $service;
    private string $package;
    private array $app;
    private string $portalFolder;


    protected function configure(): void
    {
        $this
            ->addArgument('portalName', InputArgument::REQUIRED, 'Enter name of portal')
            ->addOption('package', 'p', InputArgument::OPTIONAL, 'change package name for example:[-p or --package=com_pinoox_welcome | --p=com_pinoox_welcome]', 'pincore')
            ->addOption('service', 's', InputArgument::OPTIONAL, 'change service name for example:[-s or --service=view | --s=view]', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->portalName = $input->getArgument('portalName');
        $this->package = $input->getOption('package');
        $this->service = $input->getOption('service');
        $this->service = !empty($this->service) ? $this->service : lcfirst($this->portalName);

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

        $this->portalFolder = $this->app['path'] . 'portal';
    }

    private function create()
    {
        $exportPath = $this->readyPortal();

        $isCreated = PortalFile::createPortal(
            path: $exportPath,
            portalName: $this->portalName,
            service: $this->service,
            package: $this->app['package'],
            namespace: $this->app['namespace'] . '\portal'
        );

        if ($isCreated) {
            //print success messages
            $this->success(sprintf('Model created in "%s"', str_replace(['\\', '/'], DS, $exportPath)));
            $this->newLine();
        } else {
            $this->error(sprintf('Same file exist in "%s"!', str_replace(['\\', '/'], DS, $exportPath)));
        }
    }


    private function readyPortal(): string
    {
        //get input
        $this->portalName = Str::toCamelCase($this->portalName);

        return $this->portalFolder . DS . $this->portalName . '.php';
    }


}