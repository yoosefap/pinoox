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


namespace pinoox\terminal\portal;

use pinoox\component\helpers\PhpFile\PortalFile;
use pinoox\component\Terminal;
use pinoox\portal\AppManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePortalCommand extends Terminal
{

    protected static $defaultName = 'portal:update';

    protected static $defaultDescription = 'Update an exists portal class';

    private string $portalName;
    private string $package;
    private array $app;
    private string $portalFolder;


    protected function configure(): void
    {
        $this
            ->addArgument('portalName', InputArgument::REQUIRED, 'Enter name of portal')
            ->addOption('package', 'p', InputArgument::OPTIONAL, 'change package name for example:[-p or --package=com_pinoox_welcome | --p=com_pinoox_welcome]', 'pincore');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->portalName = $input->getArgument('portalName');
        $this->package = $input->getOption('package');
        
        $this->init();
        $this->registerPortal();

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

    private function registerPortal(): void
    {
        $path = $this->portalFolder . DS.$this->portalName.'.php';

        PortalFile::updatePortal($path, $this->portalName, $this->app['package']);
        $this->success(sprintf('Portal update in "%s".', str_replace(['\\', '/'], DS, $path)));
        $this->newLine();
    }

}