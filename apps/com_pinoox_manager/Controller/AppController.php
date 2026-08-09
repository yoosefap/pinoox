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

namespace App\com_pinoox_manager\Controller;

use App\com_pinoox_manager\Component\AppHelper;
use App\com_pinoox_manager\Component\AppIconPack;
use App\com_pinoox_manager\Component\AppLifecycle;
use App\com_pinoox_manager\Component\InstallSession;
use App\com_pinoox_manager\Component\PackageDatabase;
use App\com_pinoox_manager\Component\PackagePaths;
use App\com_pinoox_manager\Component\StorageHelper;
use App\com_pinoox_manager\Component\Wizard;
use Pinoox\Component\Http\Request;
use Pinoox\Component\Http\JsonResponse;
use Pinoox\Component\Kernel\Controller\ApiController;
use Pinoox\Component\Migration\MigrationQuery;
use Pinoox\Model\FileModel;
use Pinoox\Model\HistoryModel;
use Pinoox\Model\Table;
use Pinoox\Portal\App\AppEngine;
use Pinoox\Portal\App\AppRouter;
use Pinoox\Portal\Auth;
use Pinoox\Portal\Database\DB;
use Pinoox\Portal\Storage;

class AppController extends ApiController
{
    public function get($filter = null)
    {
        switch ($filter) {
            case 'installed':
            {
                $result = AppHelper::getAll(false);
                break;
            }
            case 'systems':
            {
                $result = AppHelper::getAll(true);
                break;
            }
            default:
            {
                $result = AppHelper::getAll(null, true);
            }
        }

        return $result;
    }

    public function getConfig($packageName)
    {
        return AppHelper::getOne($packageName);
    }

    public function setConfig(Request $request, $packageName, $key)
    {
        $config = $request->payload('config');

        if ($key == 'dock')
            $config = !$config;
        if ($key == 'router') {
            $routerConfig = AppEngine::config($packageName)->get('router');

            if (!is_array($routerConfig)) {
                $routerConfig = ['routes' => []];
            }

            $routerConfig['type'] = $config === 'multiple' ? 'single' : 'multiple';
            $config = $routerConfig;
        }

        if (!is_null($config)) {
            AppEngine::config($packageName)
                ->set($key, $config)
                ->save();

            return $this->message('manager.config_saved_successfully');
        }

        return $this->deny('manager.invalid_request');
    }

    public function install(Request $request)
    {
        $this->validated($request, [
            'file' => [
                'file',
                function ($attribute, $value, $fail) {
                    if (strtolower($value->getClientOriginalExtension()) !== 'pinx') {
                        $fail('آپلود فایل با پسوند .pinx مجاز است!');
                    }
                }
            ],
        ]);

        PackagePaths::ensureManualDir();

        $upload = $request->files->get('file');
        $filename = $upload->getClientOriginalName();
        $upload->move(PackagePaths::manualDir(), $filename);

        $pinxFile = PackagePaths::manualFile($filename);

        if (Wizard::installFromManual($pinxFile)['success']) {
            return $this->message('manager.installed_successfully');
        }

        $message = Wizard::getMessage();

        if (empty($message)) {
            return $this->error('manager.error_happened');
        }

        return $this->deny($message);
    }

    public function getAll(Request $request)
    {
        return AppHelper::getAll();
    }

    public function iconPack()
    {
        return [
            'provider' => AppIconPack::info(),
            'defaults' => AppIconPack::systemDefaults(),
            'usage' => AppIconPack::usage(),
        ];
    }

    public function installPackage($filename)
    {
        if (empty($filename))
            return $this->deny('manager.request_install_app_not_valid');

        $filename = basename($filename);
        $pinxFile = PackagePaths::manualFile($filename);
        if (!is_file($pinxFile))
            return $this->deny('manager.request_install_app_not_valid');

        $result = Wizard::installFromManual($pinxFile);

        if (!empty($result['success'])) {
            return $this->message('manager.installed_successfully', $result);
        }

        $message = Wizard::getMessage() ?: ($result['message'] ?? null);

        if (empty($message))
            return $this->deny('manager.request_install_app_not_valid');

        return $this->deny($message);
    }

    public function installPackageStart(Request $request)
    {
        $filename = basename((string) $request->payload('filename', ''));

        if ($filename === '') {
            return $this->deny('manager.request_install_app_not_valid');
        }

        $pinxFile = PackagePaths::manualFile($filename);

        if (!is_file($pinxFile)) {
            return $this->deny('manager.request_install_app_not_valid');
        }

        $sessionId = InstallSession::create($filename);
        $options = $this->installOptionsFromRequest($request);

        $database = $options['database'] ?? null;

        if (is_array($database) && PackageDatabase::hasCustomConnectionOptions($database)) {
            $connectionResult = PackageDatabase::testConnectionResult($database);

            if (!$connectionResult['ok']) {
                $message = $connectionResult['message'] ?? 'manager.database_connection_failed';

                return $this->fail(
                    'DATABASE_CONNECTION_FAILED',
                    $message,
                    status: 422,
                    translate: str_starts_with($message, 'manager.'),
                );
            }
        }

        $options['session_id'] = $sessionId;

        if (function_exists('fastcgi_finish_request')) {
            $this->sendEarlyJson($this->ok([
                'install_id' => $sessionId,
                'polling' => true,
            ]));
            @session_write_close();

            try {
                $result = Wizard::installFromManual($pinxFile, $options);
                InstallSession::complete($sessionId, $result);
            } catch (\Throwable $e) {
                InstallSession::complete($sessionId, [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'steps' => [],
                ]);
            }

            exit;
        }

        $result = Wizard::installFromManual($pinxFile, $options);
        InstallSession::complete($sessionId, $result);

        if (!empty($result['success'])) {
            return $this->message('manager.installed_successfully', $result);
        }

        $message = Wizard::getMessage() ?: ($result['message'] ?? null);

        if (empty($message)) {
            return $this->deny('manager.request_install_app_not_valid');
        }

        return $this->deny($message);
    }

    public function installPackageStatus($installId)
    {
        $session = InstallSession::get((string) $installId);

        if ($session === null) {
            return $this->deny('manager.error_happened');
        }

        return $session;
    }

    public function checkDatabasePrefix(Request $request)
    {
        $prefix = PackageDatabase::formatPrefix((string) $request->payload('prefix', ''));
        $package = (string) $request->payload('package_name', '');
        $resolved = PackageDatabase::resolvePrefix($package, $prefix);

        return [
            'prefix' => $prefix,
            'resolved_prefix' => $resolved,
            'auto_adjusted' => $resolved !== $prefix,
            'tables_exist' => PackageDatabase::prefixTablesExist($prefix),
            'tables_exist_resolved' => PackageDatabase::prefixTablesExist($resolved),
        ];
    }

    public function testDatabaseConnection(Request $request)
    {
        $input = $request->payloadMany('connection,host,database,username,password,prefix,port', '', false);
        $result = PackageDatabase::testConnectionResult($input);

        if ($result['ok']) {
            return $this->message('manager.database_connection_ok');
        }

        $message = $result['message'] ?? 'manager.database_connection_failed';

        return $this->fail(
            'DATABASE_CONNECTION_FAILED',
            $message,
            status: 422,
            translate: str_starts_with($message, 'manager.'),
        );
    }

    public function databaseDefaults()
    {
        return PackageDatabase::platformDefaults();
    }

    /**
     * @return array<string, mixed>
     */
    private function installOptionsFromRequest(Request $request): array
    {
        $database = $request->payload('database');

        if (!is_array($database) || $database === []) {
            return [];
        }

        return ['database' => $database];
    }

    private function sendEarlyJson(JsonResponse $response): void
    {
        $response->send();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    public function packageMeta(Request $request)
    {
        $filename = basename((string) $request->payload('filename', ''));

        if ($filename === '') {
            return $this->deny('manager.error_happened');
        }

        $pinxFile = PackagePaths::manualFile($filename);

        if (!is_file($pinxFile)) {
            return $this->deny('manager.error_happened');
        }

        try {
            return Wizard::pullPackageMeta($pinxFile);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function files()
    {
        $files = PackagePaths::listManualFiles();
        $files = array_map(function ($file) {
            try {
                $data = Wizard::pullPackageMeta($file);
            } catch (\Throwable) {
                Wizard::deletePackageFile($file);

                return false;
            }

            return $data;
        }, $files);

        return array_values(array_filter($files));
    }

    public function deleteFile(Request $request)
    {
        $filename = $request->payload('filename');

        if (empty($filename))
            return $this->deny('manager.error_happened');

        $pinxFile = PackagePaths::manualFile($filename);
        if (!is_file($pinxFile))
            return $this->deny('manager.error_happened');

        Wizard::deletePackageFile($pinxFile);

        return $this->message('manager.delete_successfully');
    }

    public function filesUpload(Request $request)
    {
        if (!$request->files->has('files'))
            return $this->deny('manager.invalid_request');

        $path = PackagePaths::ensureManualDir();

        $files = $request->files->all('files');
        if (!is_array($files))
            $files = [$files];

        $uploaded = 0;
        foreach ($files as $file) {
            if (strtolower($file->getClientOriginalExtension()) !== 'pinx')
                continue;
            $file->move($path, $file->getClientOriginalName());
            $uploaded++;
        }

        if ($uploaded === 0)
            return $this->deny('manager.error_happened');

        return $this->message(
            $uploaded === 1 ? 'manager.file_uploaded_correctly' : 'manager.files_uploaded_correctly'
        );
    }

    public function usage($packageName)
    {
        if (empty($packageName) || !AppEngine::exists($packageName)) {
            return $this->deny('manager.request_not_valid');
        }

        $fileCount = 0;
        $fileBytes = 0;

        try {
            $fileCount = (int) FileModel::withoutGlobalScopes()->where('app', $packageName)->count();
            $fileBytes = (int) FileModel::withoutGlobalScopes()->where('app', $packageName)->sum('file_size');
        } catch (\Throwable) {
        }

        $private = $this->directoryUsage($this->resolveStoragePath('app', $packageName));
        $public = $this->directoryUsage($this->resolveStoragePath('public', $packageName));
        $diskBytes = $private['bytes'] + $public['bytes'];
        $diskComplete = $private['complete'] && $public['complete'];
        $storageBytes = $diskComplete ? $diskBytes : max($diskBytes, $fileBytes);

        $packagePath = null;

        try {
            $packagePath = AppEngine::path($packageName);
        } catch (\Throwable) {
        }

        $package = $this->directoryUsage(is_string($packagePath) ? $packagePath : null);

        $userCount = 0;

        try {
            $users = Auth::listForApp($packageName);
            $userCount = is_array($users) ? count($users) : 0;
        } catch (\Throwable) {
        }

        $routeCount = 0;

        try {
            $routes = AppRouter::getByPackage($packageName);
            $routeCount = is_array($routes) ? count($routes) : 0;
        } catch (\Throwable) {
        }

        $migrations = $this->countAppHistory($packageName, MigrationQuery::TYPE_MIGRATION);
        $patches = $this->countAppHistory($packageName, MigrationQuery::TYPE_PATCH);

        return [
            'storage_bytes' => $storageBytes,
            'storage_label' => $this->formatBytes($storageBytes),
            'storage_complete' => $diskComplete || $fileBytes > 0 || $storageBytes === 0,
            'storage_private_bytes' => $private['bytes'],
            'storage_public_bytes' => $public['bytes'],
            'files' => $fileCount,
            'file_bytes' => $fileBytes,
            'file_label' => $this->formatBytes($fileBytes),
            'package_bytes' => $package['bytes'],
            'package_label' => $this->formatBytes($package['bytes']),
            'package_complete' => $package['complete'],
            'users' => $userCount,
            'routes' => $routeCount,
            'migrations' => $migrations,
            'patches' => $patches,
        ];
    }

    public function remove(Request $request, $packageName)
    {
        if (empty($packageName))
            return $this->deny('manager.request_not_valid');

        if (!AppEngine::exists($packageName))
            return $this->deny('manager.request_not_valid');

        $config = AppEngine::config($packageName);

        if ($config->get('sys-app')) {
            return $this->deny('manager.cannot_delete_system_app');
        }

        $purgeData = $this->boolPayload($request, 'purge_data', true);

        if (!Wizard::deleteApp($packageName, [
            'purge_data' => $purgeData,
            'purge_storage' => $purgeData,
        ])) {
            $message = Wizard::getMessage();

            if (empty($message)) {
                return $this->deny('manager.error_happened');
            }

            return $this->deny($message);
        }

        return $this->message('manager.delete_successfully');
    }

    public function reset(Request $request, $packageName)
    {
        if (empty($packageName))
            return $this->deny('manager.request_not_valid');

        if (!AppEngine::exists($packageName))
            return $this->deny('manager.request_not_valid');

        $config = AppEngine::config($packageName);

        if ($config->get('sys-app')) {
            return $this->deny('manager.cannot_reset_system_app');
        }

        $result = AppLifecycle::reset($packageName, [
            'purge_storage' => $this->boolPayload($request, 'purge_storage', true),
        ]);

        if ($result === false) {
            $message = AppLifecycle::getMessage();

            if (empty($message)) {
                return $this->deny('manager.error_happened');
            }

            return $this->deny($message);
        }

        return $this->message('manager.reset_successfully', $result);
    }

    private function countAppHistory(string $packageName, string $type): int
    {
        if ($packageName === '' || $packageName === 'platform') {
            return 0;
        }

        try {
            return (int) DB::table(DB::tableName(Table::HISTORY, 'platform'), null, 'platform')
                ->where('app', $packageName)
                ->where('type', $type)
                ->count();
        } catch (\Throwable) {
            try {
                return (int) HistoryModel::query()
                    ->where('app', $packageName)
                    ->where('type', $type)
                    ->count();
            } catch (\Throwable) {
                return 0;
            }
        }
    }

    private function resolveStoragePath(string $kind, string $package): ?string
    {
        try {
            $path = $kind === 'public'
                ? Storage::publicPath($package)
                : Storage::appPath($package);

            return is_string($path) && $path !== '' ? $path : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{bytes: int, complete: bool}
     */
    private function directoryUsage(?string $path): array
    {
        if (!is_string($path) || $path === '' || !is_dir($path)) {
            return ['bytes' => 0, 'complete' => true];
        }

        $scan = StorageHelper::directorySizeBytes($path, 12, true);

        return [
            'bytes' => (int) ($scan['bytes'] ?? 0),
            'complete' => !empty($scan['complete']),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = (float) $bytes;

        foreach ($units as $unit) {
            $value /= 1024;

            if ($value < 1024) {
                $decimals = $value < 10 ? 2 : ($value < 100 ? 1 : 0);

                return rtrim(rtrim(number_format($value, $decimals, '.', ''), '0'), '.') . ' ' . $unit;
            }
        }

        return number_format($value / 1024, 1, '.', '') . ' PB';
    }

    private function boolPayload(Request $request, string $key, bool $default = false): bool
    {
        $value = $request->payload($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off', ''], true)) {
                return false;
            }
        }

        return (bool) $value;
    }
}
