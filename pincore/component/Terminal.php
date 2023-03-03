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

namespace pinoox\component;

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Terminal extends Command
{
    protected InputInterface $input;
    protected OutputInterface $output;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        return Command::SUCCESS;
    }

    protected function info($message)
    {
        $this->output->write($message);
    }

    #[NoReturn] protected function error($message): void
    {
        $this->output->write("<error>$message</error>");
        exit;
    }

    #[NoReturn] protected function success($message): void
    {
        $this->output->write("<info>$message</info>");
    }

    #[NoReturn] protected function question($message): void
    {
        $this->output->write("<question>$message</question>");
    }

    #[NoReturn] protected function warning($message): void
    {
        $this->output->write("<comment>$message</comment>");
    }

    #[NoReturn] protected function newline(): void
    {
        $this->output->writeln('');
    }

    #[NoReturn] protected function stop(): void
    {
        exit;
    }

    protected function table($columns, $rows)
    {
        $table = new Table($this->output);
        $table->setHeaders($columns)
            ->setRows($rows);
        $table->render();
    }


}