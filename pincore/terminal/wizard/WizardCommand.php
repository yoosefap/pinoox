<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace pinoox\terminal\wizard;

use pinoox\component\Terminal;
use pinoox\portal\AppWizard;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'wizard',
    description: 'Install apps or templates',
)]
class WizardCommand extends Terminal
{
    const PATH = PINOOX_PATH . 'pins' . DS;

    protected function configure(): void
    {
        $this
            ->addArgument('package', InputArgument::REQUIRED, 'Enter package name')
            ->addOption('force', 'f', null, 'force install if it is already exists. example:[wizard [package_name] -f]');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

      
        $package = $input->getArgument('package');
        $force = $input->getOption('force')  ?? null;

        $package = str_replace('.pin', '', $package);
        $pin = self::PATH . $package . '.pin';

        if (!file_exists($pin)) {
            $this->error('package file not found: "' . $pin . '"');
        }

        $wizard = AppWizard::open($pin);

        $wizard->force($force);

        if ($wizard->isInstalled() && !$force) {
            // Continue installation
            $confirm = $this->confirm('The package already exists, Do you want to continue installation? (yes/no) ', $input, $output);
            if ($confirm) {
                $wizard->force();
            } else {
                $this->error('Installation canceled.');
            }

        }

        $result = $wizard->install();
        $this->success($result['message'] . ': "' . $package.'"');

        return Command::SUCCESS;
    }

}