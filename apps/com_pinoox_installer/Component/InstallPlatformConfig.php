<?php

namespace App\com_pinoox_installer\Component;

use Pinoox\Component\Database\DatabaseManager;
use Pinoox\Portal\Config;
use Pinoox\Support\SystemConfig;

/**
 * Local install-platform.php overlay (gitignored under .pinoox/).
 */
final class InstallPlatformConfig
{
    public const FILENAME = 'install-platform.php';

    /**
     * @return array{
     *     lang: string,
     *     db: array<string, mixed>,
     *     user: array<string, mixed>
     * }
     */
    public static function defaults(): array
    {
        $db = [
            'connection' => 'mysql',
            'host' => '127.0.0.1',
            'port' => InstallerDatabase::defaultPort('mysql'),
            'database' => 'pinoox',
            'username' => 'root',
            'password' => '',
            'prefix' => DatabaseManager::DEFAULT_CORE_TABLE_PREFIX,
            'timezone' => '+03:30',
        ];

        $db = self::overlayStoredDatabase($db);
        $db = self::overlayEnvDatabase($db);

        $lang = self::stringEnv('APP_LOCALE') ?: 'en';
        if (!in_array($lang, ['en', 'fa'], true)) {
            $lang = 'en';
        }

        return [
            'lang' => $lang,
            'db' => $db,
            'user' => [
                'fname' => 'Admin',
                'lname' => 'Pinoox',
                'email' => 'admin@example.com',
                'username' => 'admin',
                'password' => '',
            ],
        ];
    }

    public static function defaultPath(): string
    {
        return rtrim(str_replace('\\', '/', SystemConfig::rootPath()), '/') . '/.pinoox/' . self::FILENAME;
    }

    public static function resolvePath(?string $file): string
    {
        $file = trim(str_replace('\\', '/', (string) $file));

        if ($file === '') {
            return self::defaultPath();
        }

        if (preg_match('/^[A-Za-z]:\//', $file) === 1 || str_starts_with($file, '/')) {
            return $file;
        }

        return rtrim(str_replace('\\', '/', SystemConfig::rootPath()), '/') . '/' . ltrim($file, '/');
    }

    public static function exists(string $path): bool
    {
        return is_file($path);
    }

    public static function remove(string $path): bool
    {
        if (!is_file($path)) {
            return true;
        }

        return @unlink($path);
    }

    /**
     * @param array{
     *     lang?: string,
     *     db?: array<string, mixed>,
     *     user?: array<string, mixed>
     * }|null $defaults
     */
    public static function writeStub(string $path, bool $force = false, ?array $defaults = null): void
    {
        if (is_file($path) && !$force) {
            throw new InstallPlatformException(
                'Config already exists: ' . $path . '. Edit it, or pass --force to overwrite.',
            );
        }

        $directory = dirname($path);
        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new InstallPlatformException('Could not create directory: ' . $directory);
        }

        $payload = self::mergeDefaults($defaults ?? self::defaults());
        $written = @file_put_contents($path, self::renderStub($payload), LOCK_EX);

        if ($written === false) {
            throw new InstallPlatformException('Could not write config: ' . $path);
        }
    }

    /**
     * @return array{
     *     lang: string,
     *     db: array<string, mixed>,
     *     user: array<string, mixed>
     * }
     */
    public static function load(string $path): array
    {
        if (!is_file($path)) {
            throw new InstallPlatformException(
                'Config not found: ' . $path . '. Run: php pinoox install-platform init',
            );
        }

        $data = include $path;

        if (!is_array($data)) {
            throw new InstallPlatformException('Invalid config file. Expected return [\'lang\' => …, \'db\' => …, \'user\' => …];');
        }

        return self::validate($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{
     *     lang: string,
     *     db: array<string, mixed>,
     *     user: array<string, mixed>
     * }
     */
    public static function validate(array $data): array
    {
        $errors = [];
        $db = is_array($data['db'] ?? null) ? $data['db'] : [];
        $user = is_array($data['user'] ?? null) ? $data['user'] : [];
        $lang = strtolower(trim((string) ($data['lang'] ?? 'en')));

        if ($lang === '') {
            $lang = 'en';
        }

        if (!preg_match('/^[a-z]{2}(?:[_-][a-zA-Z0-9]+)?$/', $lang)) {
            $errors[] = 'lang must be a locale code such as en or fa.';
        }

        $connection = strtolower(trim((string) ($db['connection'] ?? 'mysql')));
        if ($connection === '') {
            $connection = 'mysql';
        }

        if (!in_array($connection, InstallerDatabase::INSTALLABLE_CONNECTIONS, true)) {
            $errors[] = 'db.connection must be one of: ' . implode(', ', InstallerDatabase::INSTALLABLE_CONNECTIONS) . '.';
        }

        foreach (['host' => 'db.host', 'database' => 'db.database', 'username' => 'db.username'] as $key => $label) {
            if (trim((string) ($db[$key] ?? '')) === '') {
                $errors[] = $label . ' is required.';
            }
        }

        $fname = trim((string) ($user['fname'] ?? ''));
        $lname = trim((string) ($user['lname'] ?? ''));
        $email = trim((string) ($user['email'] ?? ''));
        $username = trim((string) ($user['username'] ?? ''));
        $password = (string) ($user['password'] ?? '');

        if (strlen($fname) < 3) {
            $errors[] = 'user.fname is required (min 3 characters).';
        }

        if (strlen($lname) < 3) {
            $errors[] = 'user.lname is required (min 3 characters).';
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'user.email must be a valid email address.';
        }

        if (strlen($username) < 3 || preg_match('/^[A-Za-z0-9_-]+$/', $username) !== 1) {
            $errors[] = 'user.username is required (min 3, letters/numbers/_/-).';
        }

        if (strlen($password) < 6) {
            $errors[] = 'user.password is required (min 6 characters).';
        }

        if ($errors !== []) {
            throw new InstallPlatformException('Invalid install-platform config.', $errors);
        }

        $normalizedDb = [
            'connection' => $connection,
            'host' => trim((string) $db['host']),
            'database' => trim((string) $db['database']),
            'username' => trim((string) $db['username']),
            'password' => (string) ($db['password'] ?? ''),
            'prefix' => (string) ($db['prefix'] ?? DatabaseManager::DEFAULT_CORE_TABLE_PREFIX),
            'port' => trim((string) ($db['port'] ?? '')),
            'timezone' => trim((string) ($db['timezone'] ?? '+03:30')),
        ];

        return [
            'lang' => $lang,
            'db' => $normalizedDb,
            'user' => [
                'fname' => $fname,
                'lname' => $lname,
                'email' => $email,
                'username' => $username,
                'password' => $password,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $db
     * @return array<string, mixed>
     */
    public static function dbInput(array $db): array
    {
        $connection = InstallerDatabase::connectionName($db);

        return array_merge(
            ['connection' => $connection],
            InstallerDatabase::normalize($db),
        );
    }

    /**
     * @param array{
     *     lang: string,
     *     db: array<string, mixed>,
     *     user: array<string, mixed>
     * } $payload
     */
    public static function renderStub(array $payload): string
    {
        $payload = self::mergeDefaults($payload);

        $e = static fn (mixed $value): string => var_export($value, true);

        return <<<PHP
<?php

/**
 * Pinoox CLI platform install overlay (gitignored with .pinoox/).
 *
 * Fill in database + admin user, then run:
 *   php pinoox install-platform run
 */

return [
    'lang' => {$e($payload['lang'])},

    'db' => [
        'connection' => {$e($payload['db']['connection'])}, // mysql | mariadb | pgsql | sqlsrv
        'host' => {$e($payload['db']['host'])},
        'port' => {$e($payload['db']['port'])},
        'database' => {$e($payload['db']['database'])},
        'username' => {$e($payload['db']['username'])},
        'password' => {$e($payload['db']['password'])},
        'prefix' => {$e($payload['db']['prefix'])},
        'timezone' => {$e($payload['db']['timezone'])},
    ],

    'user' => [
        'fname' => {$e($payload['user']['fname'])},
        'lname' => {$e($payload['user']['lname'])},
        'email' => {$e($payload['user']['email'])},
        'username' => {$e($payload['user']['username'])},
        'password' => {$e($payload['user']['password'])},
    ],
];

PHP;
    }

    /**
     * @param array<string, mixed> $data
     * @return array{
     *     lang: string,
     *     db: array<string, mixed>,
     *     user: array<string, mixed>
     * }
     */
    private static function mergeDefaults(array $data): array
    {
        $defaults = [
            'lang' => 'en',
            'db' => [
                'connection' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'database' => 'pinoox',
                'username' => 'root',
                'password' => '',
                'prefix' => DatabaseManager::DEFAULT_CORE_TABLE_PREFIX,
                'timezone' => '+03:30',
            ],
            'user' => [
                'fname' => 'Admin',
                'lname' => 'Pinoox',
                'email' => 'admin@example.com',
                'username' => 'admin',
                'password' => '',
            ],
        ];

        $db = array_replace($defaults['db'], is_array($data['db'] ?? null) ? $data['db'] : []);
        $user = array_replace($defaults['user'], is_array($data['user'] ?? null) ? $data['user'] : []);

        return [
            'lang' => (string) ($data['lang'] ?? $defaults['lang']),
            'db' => $db,
            'user' => $user,
        ];
    }

    /**
     * @param array<string, mixed> $db
     * @return array<string, mixed>
     */
    private static function overlayEnvDatabase(array $db): array
    {
        $map = [
            'DB_HOST' => 'host',
            'DB_PORT' => 'port',
            'DB_DATABASE' => 'database',
            'DB_USERNAME' => 'username',
            'DB_PASSWORD' => 'password',
            'DB_PREFIX' => 'prefix',
            'DB_TIMEZONE' => 'timezone',
        ];

        foreach ($map as $env => $key) {
            $value = self::stringEnv($env);
            if ($value !== null) {
                $db[$key] = $value;
            }
        }

        $connection = self::stringEnv('DB_CONNECTION');
        if ($connection !== null && in_array(strtolower($connection), InstallerDatabase::INSTALLABLE_CONNECTIONS, true)) {
            $db['connection'] = strtolower($connection);
            if (trim((string) ($db['port'] ?? '')) === '') {
                $db['port'] = InstallerDatabase::defaultPort($db['connection']);
            }
        }

        return $db;
    }

    /**
     * @param array<string, mixed> $db
     * @return array<string, mixed>
     */
    private static function overlayStoredDatabase(array $db): array
    {
        try {
            $stored = Config::name('~database')->get();
        } catch (\Throwable) {
            return $db;
        }

        if (!is_array($stored)) {
            return $db;
        }

        $connections = is_array($stored['connections'] ?? null) ? $stored['connections'] : [];
        $default = strtolower(trim((string) ($stored['default'] ?? 'mysql')));
        $name = in_array($default, InstallerDatabase::INSTALLABLE_CONNECTIONS, true)
            ? $default
            : 'mysql';
        $block = is_array($connections[$name] ?? null) ? $connections[$name] : [];

        if ($block === []) {
            return $db;
        }

        $db['connection'] = $name;

        foreach (['host', 'port', 'database', 'username', 'password', 'prefix', 'timezone'] as $key) {
            if (array_key_exists($key, $block) && $block[$key] !== null && $block[$key] !== '') {
                $db[$key] = is_scalar($block[$key]) ? (string) $block[$key] : $db[$key];
            }
        }

        return $db;
    }

    private static function stringEnv(string $key): ?string
    {
        $value = SystemConfig::env($key);

        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
