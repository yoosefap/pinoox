<?php

namespace App\com_pinoox_installer\Component;

use Pinoox\Component\Package\AppProvisioner;
use Pinoox\Component\Package\Engine\AppEngine;
use Pinoox\Portal\App\AppEngine as AppEnginePortal;
use Pinoox\Portal\App\AppRouter;
use Pinoox\Portal\Database\DB;
use Pinoox\Portal\Logger;
use Pinoox\Model\Table;
use Pinoox\Model\UserModel;
use Pinoox\Support\SystemConfig;

final class SetupService
{
    public function __construct(
        private readonly AppEngine $engine,
    ) {
    }

    /**
     * @param array<string, mixed> $dbInput
     * @param array<string, mixed> $userInput
     */
    public function run(array $dbInput, array $userInput, ?string $lang = null): void
    {
        @set_time_limit(600);
        ignore_user_abort(true);

        $this->prepareDatabase($dbInput);

        try {
            $this->migrateTables();
            $this->runPatches();

            if (!$this->ensureAdminUser($userInput)) {
                throw new SetupException('install.err_insert_tables', 'Could not create the admin user.');
            }

            $this->configureApps($lang);
        } catch (SetupException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Logger::error('Installer setup failed: ' . $e->getMessage(), [
                'exception' => $e,
                'migration_path' => SystemConfig::platformPath('migrations'),
                'patch_path' => SystemConfig::platformPath('patches'),
            ]);

            throw new SetupException('install.err_provision', $e->getMessage(), $e);
        }

        try {
            $this->disableInstaller();
        } catch (\Throwable $e) {
            Logger::error('Installer disable step failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            throw new SetupException('install.err_provision', $e->getMessage(), $e);
        }
    }

    /**
     * @param array<string, mixed> $dbInput
     */
    private function prepareDatabase(array $dbInput): void
    {
        if ($dbInput === []) {
            throw new SetupException('install.err_insert_tables', 'Database credentials were empty.');
        }

        $config = InstallerDatabase::normalize($dbInput);
        $connectionName = InstallerDatabase::connectionName($dbInput);
        $this->applyInstallConnection($connectionName, $config);

        $error = null;
        if (!InstallerDatabase::testConnection($dbInput, $error)) {
            throw new SetupException(
                'install.err_insert_tables',
                'Database connection failed: ' . ($error ?: 'unknown error'),
            );
        }

        if (!DatabaseCredentialsSync::persist($config, $connectionName)
            && !$this->persistDatabaseFallback($config, $connectionName)
        ) {
            throw new SetupException(
                'install.err_insert_tables',
                'Could not write pinker/stable/platform/database.config.php (check permissions).',
            );
        }

        $this->reconnectDatabase($config);
    }

    private function migrateTables(): void
    {
        $this->provisioner()->provisionCore(['skip_patch' => true]);

        if (!$this->coreTablesReady()) {
            throw new SetupException('install.err_insert_tables', 'Core tables were not created after migration.');
        }

        $this->provisioner()->migratePackages($this->projectPackages());
    }

    private function runPatches(): void
    {
        $this->provisioner()->provisionCore(['skip_migrate' => true]);
        $this->provisioner()->patchPackages($this->projectPackages());
    }

    private function configureApps(?string $lang): void
    {
        $this->provisioner()->applyLangToPackages($this->projectPackages(), $lang);
    }

    /**
     * @return list<string>
     */
    private function projectPackages(): array
    {
        return $this->provisioner()->packagesForSetup([
            'exclude' => ['com_pinoox_installer'],
            'only_enabled' => true,
        ]);
    }

    private function disableInstaller(): void
    {
        $routesFile = AppEnginePortal::path('com_pinoox_installer') . '/config/app.config.php';
        $postInstallRoutes = is_file($routesFile) ? require $routesFile : [
            '/' => 'com_pinoox_welcome',
            '/manager' => 'com_pinoox_manager',
        ];

        try {
            AppRouter::setData(is_array($postInstallRoutes) ? $postInstallRoutes : []);
        } catch (\Throwable $e) {
            Logger::error('Installer app-router swap failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }

        AppEnginePortal::config('com_pinoox_installer')->set('enable', false)->save();
    }

    private function reconnectDatabase(array $config): void
    {
        SystemConfig::clearCache();
        if (method_exists(DB::class, 'refreshCoreConnection')) {
            DB::refreshCoreConnection($config);

            return;
        }

        DB::addConnection($config, 'default');
        DB::addConnection($config, 'platform');
        DB::bootEloquent();
    }

    private function applyInstallConnection(string $connectionName, array $config = []): void
    {
        $vars = [
            'APP_ENV' => 'production',
            'MODE' => 'production',
            'DB_CONNECTION' => $connectionName,
            'DB_HOST' => (string) ($config['host'] ?? ''),
            'DB_PORT' => (string) ($config['port'] ?? ''),
            'DB_DATABASE' => (string) ($config['database'] ?? ''),
            'DB_USERNAME' => (string) ($config['username'] ?? ''),
            'DB_PASSWORD' => (string) ($config['password'] ?? ''),
            'DB_PREFIX' => (string) ($config['prefix'] ?? ''),
            'DB_TIMEZONE' => (string) ($config['timezone'] ?? ''),
        ];

        foreach ($vars as $key => $value) {
            if ($value === '' && $key !== 'DB_PASSWORD') {
                continue;
            }
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key . '=' . $value);
        }

        SystemConfig::clearCache();
    }

    /**
     * @param array<string, mixed> $config
     */
    private function persistDatabaseFallback(array $config, string $connectionName): bool
    {
        $file = SystemConfig::pinkerStableConfigPath('database');
        $dir = dirname($file);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }

        $payload = [
            'default' => $connectionName,
            'connections' => [
                $connectionName => $config,
            ],
        ];
        $export = var_export($payload, true);

        return @file_put_contents($file, "<?php\n\nreturn {$export};\n") !== false;
    }

    private function coreTablesReady(): bool
    {
        foreach ([Table::HISTORY, Table::USER, Table::TOKEN, Table::FILE, Table::ROLE] as $table) {
            try {
                $physical = DB::physicalTableName($table, 'platform');
                $connection = DB::connection('platform');
                $database = (string) $connection->getDatabaseName();

                if ($database === '' || $physical === '') {
                    return false;
                }

                $row = $connection->selectOne(
                    'SELECT 1 AS found FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1',
                    [$database, $physical],
                );

                if ($row === null) {
                    Logger::error('Installer missing core table after migration: ' . $physical);

                    return false;
                }
            } catch (\Throwable $e) {
                Logger::error('Installer core table check failed: ' . $e->getMessage(), ['exception' => $e]);

                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $userInput
     */
    private function ensureAdminUser(array $userInput): bool
    {
        try {
            $username = (string) ($userInput['username'] ?? '');

            if ($username === '') {
                return false;
            }

            $exists = UserModel::withoutGlobalScopes()
                ->where('app', 'platform')
                ->where('username', $username)
                ->exists();

            if ($exists) {
                return true;
            }

            return (bool) UserModel::withoutGlobalScopes()->create([
                'app' => 'platform',
                'group_key' => 'admin',
                'fname' => $userInput['fname'],
                'lname' => $userInput['lname'],
                'username' => $username,
                'password' => $userInput['password'],
                'email' => $userInput['email'],
            ]);
        } catch (\Throwable $e) {
            Logger::error('Installer admin user creation failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return false;
        }
    }

    private function provisioner(): AppProvisioner
    {
        return new AppProvisioner($this->engine);
    }

    public static function make(): self
    {
        return new self(AppEnginePortal::___());
    }
}
