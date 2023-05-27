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

use pinoox\component\kernel\Exception;
use pinoox\component\Terminal;
use pinoox\portal\AppEngine;
use pinoox\portal\AppWizard;
use pinoox\portal\Path;
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
        $this->addArgument('app', InputArgument::OPTIONAL, 'Enter app package name')
            ->addArgument('path', InputArgument::OPTIONAL, 'Enter path pin package');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $app = $input->getArgument('app');
        $path = $input->getArgument('path');

        if (empty($app) && empty($path)) {
            $this->error('Enter app or path option to install pin');
        }

        if (!empty($app) && !file_exists(self::PATH . $app . '.pin')) {
            $this->error('App not found: "' . self::PATH . $app . '.pin"');
        }

        if (!empty($path) && !file_exists($path)) {
            $this->error('Pin not found in path: "' . $path);
        }

        if (!empty($app)) {
            $pin = self::PATH . $app . '.pin';
            if (!file_exists($pin)) {
                $this->error('App not found: "' . $pin . '"');
            }
        } elseif (!empty($path)) {
            if (!file_exists($path)) {
                $this->error('Pin not found at path: "' . $path . '"');
            }
            $pin = $path;
        }

        $wizard = AppWizard::open($pin);
        $wizard->install();

        $this->success('App installed successfully: '. $pin);

        return Command::SUCCESS;
    }

}