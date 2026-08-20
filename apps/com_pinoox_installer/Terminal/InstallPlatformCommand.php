<?php

namespace App\com_pinoox_installer\Terminal;

use App\com_pinoox_installer\Component\HtaccessManager;
use App\com_pinoox_installer\Component\InstallerDatabase;
use App\com_pinoox_installer\Component\InstallPlatformConfig;
use App\com_pinoox_installer\Component\InstallPlatformException;
use App\com_pinoox_installer\Component\PrerequisitesChecker;
use App\com_pinoox_installer\Component\SetupException;
use App\com_pinoox_installer\Component\SetupService;
use Pinoox\Component\Terminal;
use Pinoox\Portal\App\AppEngine;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'install-platform',
    description: 'Install the platform from a local config file (same steps as the web installer)',
    aliases: ['platform:install'],
)]
class InstallPlatformCommand extends Terminal
{
    protected function configure(): void
    {
        $this
            ->setHelp(
                <<<'HELP'
First-time platform setup without the web installer.

  php pinoox install-platform init
  # edit .pinoox/install-platform.php (database + admin user + lang)
  php pinoox install-platform run

Other:
  php pinoox install-platform check
  php pinoox install-platform init --force
  php pinoox install-platform run --dry-run
  php pinoox install-platform run --file=.pinoox/install-platform.php

This runs the same SetupService as the GUI installer: save DB credentials,
migrate core + apps, run patches, create the admin user, apply language,
swap app-router to welcome/manager, and disable the installer app.
HELP
            )
            ->addArgument('action', InputArgument::OPTIONAL, 'init, run, or check')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'Config file path (default: .pinoox/install-platform.php)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite the stub (init) or re-run setup if already installed')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate the config without installing (run)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $io = new SymfonyStyle($input, $output);
        $action = strtolower(trim((string) ($input->getArgument('action') ?: '')));
        $path = InstallPlatformConfig::resolvePath(
            is_string($input->getOption('file')) ? $input->getOption('file') : null,
        );

        return match ($action) {
            '', 'help' => $this->showUsage($io, $path),
            'init' => $this->init($input, $io, $path),
            'run' => $this->runSetup($input, $io, $path),
            'check' => $this->check($io, $path),
            default => $this->unknownAction($io, $action),
        };
    }

    private function showUsage(SymfonyStyle $io, string $path): int
    {
        $io->title('Install platform from CLI');
        $io->writeln('Default config: <comment>' . $path . '</comment>');
        $io->newLine();
        $io->listing([
            'php pinoox install-platform init',
            'edit the config file (database, admin user, lang)',
            'php pinoox install-platform run',
        ]);

        return Command::SUCCESS;
    }

    private function unknownAction(SymfonyStyle $io, string $action): int
    {
        $io->error("Unknown action '$action'. Use init, run, or check.");

        return Command::INVALID;
    }

    private function init(InputInterface $input, SymfonyStyle $io, string $path): int
    {
        try {
            InstallPlatformConfig::writeStub($path, (bool) $input->getOption('force'));
        } catch (InstallPlatformException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Wrote ' . $path);
        $io->writeln('Edit database, admin user, and lang, then run:');
        $io->writeln('  <info>php pinoox install-platform run</info>');

        return Command::SUCCESS;
    }

    private function check(SymfonyStyle $io, string $path): int
    {
        try {
            $payload = InstallPlatformConfig::load($path);
        } catch (InstallPlatformException $e) {
            return $this->failConfig($io, $e);
        }

        $db = InstallPlatformConfig::dbInput($payload['db']);

        $io->definitionList(
            ['Config' => $path],
            ['Lang' => $payload['lang']],
            ['Connection' => (string) $db['connection']],
            ['Host' => (string) ($db['host'] ?? '')],
            ['Database' => (string) ($db['database'] ?? '')],
            ['Admin' => (string) $payload['user']['username']],
        );

        if (!InstallerDatabase::testConnection($db)) {
            $io->error('Cannot connect to the database with these credentials.');

            return Command::FAILURE;
        }

        $io->success('Database connection OK.');

        return Command::SUCCESS;
    }

    private function runSetup(InputInterface $input, SymfonyStyle $io, string $path): int
    {
        $dryRun = (bool) $input->getOption('dry-run');
        $force = (bool) $input->getOption('force');

        try {
            $payload = InstallPlatformConfig::load($path);
        } catch (InstallPlatformException $e) {
            return $this->failConfig($io, $e);
        }

        if (!$dryRun && $this->isAlreadyInstalled() && !$force) {
            $io->error('Pinoox looks already installed (installer is disabled). Pass --force to run setup again.');

            return Command::FAILURE;
        }

        $phpCheck = (new PrerequisitesChecker())->check('php');
        if (($phpCheck['state'] ?? '') === 'fail') {
            $required = (string) ($phpCheck['required'] ?? '');
            $current = (string) ($phpCheck['current'] ?? PHP_VERSION);
            $io->error('PHP ' . $required . ' or newer is required (current: ' . $current . ').');

            return Command::FAILURE;
        }

        $db = InstallPlatformConfig::dbInput($payload['db']);

        $io->definitionList(
            ['Config' => $path],
            ['Mode' => $dryRun ? 'dry-run' : 'install'],
            ['Lang' => $payload['lang']],
            ['Connection' => (string) $db['connection']],
            ['Host' => (string) ($db['host'] ?? '') . ':' . (string) ($db['port'] ?? '')],
            ['Database' => (string) ($db['database'] ?? '')],
            ['Admin' => (string) $payload['user']['username']],
        );

        if ($dryRun) {
            $io->success('Config is valid. Run without --dry-run to install.');

            return Command::SUCCESS;
        }

        if (!InstallerDatabase::testConnection($db)) {
            $io->error('Cannot connect to the database with these credentials.');

            return Command::FAILURE;
        }

        $htaccess = (new HtaccessManager())->create();
        if (!empty($htaccess['created'])) {
            $io->writeln('Wrote project <comment>.htaccess</comment>.');
        }

        try {
            SetupService::make()->run($db, $payload['user'], $payload['lang']);
        } catch (SetupException $e) {
            $io->error($this->setupErrorMessage($e));

            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error('Installer setup failed: ' . $e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Pinoox was installed successfully.');
        $io->listing([
            '/  → com_pinoox_welcome',
            '/manager  → com_pinoox_manager',
        ]);
        $io->note('Config still contains secrets: ' . $path);

        return Command::SUCCESS;
    }

    private function isAlreadyInstalled(): bool
    {
        try {
            return !AppEngine::stable('com_pinoox_installer');
        } catch (\Throwable) {
            return false;
        }
    }

    private function failConfig(SymfonyStyle $io, InstallPlatformException $e): int
    {
        $io->error($e->getMessage());

        foreach ($e->errors() as $error) {
            $io->writeln('  - ' . $error);
        }

        return Command::FAILURE;
    }

    private function setupErrorMessage(SetupException $e): string
    {
        return match ($e->messageKey()) {
            'install.err_insert_tables' => 'Could not connect to the database or create tables.',
            'install.err_provision' => 'Database setup finished but app configuration failed.',
            default => $e->getMessage(),
        };
    }
}
