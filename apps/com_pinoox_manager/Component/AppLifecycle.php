<?php

namespace App\com_pinoox_manager\Component;

use Pinoox\Component\Cache\AppCacheManager;
use Pinoox\Component\Database\Patch\PatchToolkit;
use Pinoox\Component\File\FileStorage;
use Pinoox\Component\Migration\MigrationQuery;
use Pinoox\Component\Migration\Migrator;
use Pinoox\Model\FileModel;
use Pinoox\Model\HistoryModel;
use Pinoox\Portal\FileSystem;
use Pinoox\Portal\Storage;

/**
 * Reset / purge helpers for installed apps (manager control panel).
 */
final class AppLifecycle
{
    private static ?string $message = null;

    public static function getMessage(): ?string
    {
        $message = self::$message;
        self::$message = null;

        return $message;
    }

    /**
     * Wipe app data and re-run migrations + patches.
     *
     * @param array{purge_storage?: bool} $options
     * @return array{steps: list<array{step: string, status: string, message: string}>}|false
     */
    public static function reset(string $package, array $options = []): array|false
    {
        self::$message = null;
        $purgeStorage = (bool) ($options['purge_storage'] ?? true);
        $steps = [];

        try {
            if ($purgeStorage) {
                $purged = self::purgeStorage($package);
                $steps[] = self::step(
                    'storage',
                    'ok',
                    sprintf('پاک‌سازی فایل‌های storage (%d رکورد).', $purged['files']),
                );
            } else {
                $steps[] = self::step('storage', 'skipped', 'پاک‌سازی storage رد شد.');
            }

            self::clearPatchHistory($package);
            $steps[] = self::step('patch', 'ok', 'تاریخچه پچ‌ها پاک شد.');

            (new Migrator('platform'))->run();
            $migrateMessages = (new Migrator($package))->fresh();
            $steps[] = self::step(
                'migrate',
                'ok',
                'مایگریشن‌ها از نو اجرا شدند (' . count($migrateMessages) . ' پیام).',
            );

            $patchResult = self::runPatches($package);
            $steps[] = self::step(
                'patch',
                $patchResult['ran'] > 0 ? 'ok' : 'skipped',
                $patchResult['ran'] > 0
                    ? sprintf('%d پچ اجرا شد.', $patchResult['ran'])
                    : 'پچی برای اجرا نبود.',
            );

            AppCacheManager::clear($package);
            AppCacheManager::build($package, null, true);
            $steps[] = self::step('cache', 'ok', 'کش اپلیکیشن بازسازی شد.');

            return ['steps' => $steps];
        } catch (\Throwable $e) {
            self::$message = $e->getMessage();

            return false;
        }
    }

    /**
     * Delete file table rows and leftover package storage folders.
     *
     * @return array{files: int, directories: list<string>}
     */
    public static function purgeStorage(string $package): array
    {
        $deletedFiles = 0;

        FileModel::withoutGlobalScopes()
            ->where('app', $package)
            ->orderBy('file_id')
            ->chunkById(100, function ($files) use (&$deletedFiles): void {
                foreach ($files as $file) {
                    try {
                        // Model delete event removes storage assets via FileStorage::delete.
                        if ($file->delete()) {
                            $deletedFiles++;
                        }
                    } catch (\Throwable) {
                        try {
                            FileStorage::delete($file);
                        } catch (\Throwable) {
                        }

                        FileModel::withoutEvents(static function () use ($file): void {
                            $file->delete();
                        });
                        $deletedFiles++;
                    }
                }
            });

        $removedDirs = [];

        $paths = [];

        try {
            $paths[] = Storage::appPath($package);
        } catch (\Throwable) {
        }

        try {
            $paths[] = Storage::publicPath($package);
        } catch (\Throwable) {
        }

        foreach (array_unique(array_filter($paths, 'is_string')) as $path) {
            if ($path === '' || (!is_dir($path) && !is_file($path))) {
                continue;
            }

            try {
                FileSystem::remove($path);
                $removedDirs[] = $path;
            } catch (\Throwable) {
            }
        }

        return [
            'files' => $deletedFiles,
            'directories' => $removedDirs,
        ];
    }

    private static function clearPatchHistory(string $package): void
    {
        HistoryModel::where('type', MigrationQuery::TYPE_PATCH)
            ->where('app', $package)
            ->delete();
    }

    /**
     * @return array{ran: int, skipped: int}
     */
    private static function runPatches(string $package): array
    {
        $toolkit = new PatchToolkit();
        $toolkit->package($package)->load();

        if (!$toolkit->isSuccess()) {
            throw new \RuntimeException((string) $toolkit->getErrors());
        }

        $ran = 0;
        $skipped = 0;

        foreach ($toolkit->getPatches() as $patch) {
            if (!empty($patch['ran'])) {
                $skipped++;
                continue;
            }

            if (empty($patch['should_run'])) {
                $toolkit->recordSkipped($patch['name'], $patch['checksum'] ?? null);
                $skipped++;
                continue;
            }

            $startedAt = microtime(true);
            $patch['instance']->run();
            $toolkit->recordSuccess(
                $patch['name'],
                $patch['checksum'] ?? null,
                (int) round((microtime(true) - $startedAt) * 1000),
            );
            $ran++;
        }

        return ['ran' => $ran, 'skipped' => $skipped];
    }

    /**
     * @return array{step: string, status: string, message: string}
     */
    private static function step(string $step, string $status, string $message): array
    {
        return [
            'step' => $step,
            'status' => $status,
            'message' => $message,
        ];
    }
}
