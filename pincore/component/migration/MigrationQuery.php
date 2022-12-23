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

namespace pinoox\component\migration;

use pinoox\model\MigrationModel;

class MigrationQuery
{

    public static function fetchLatestBatch($app): int
    {
        return MigrationModel::where('app', $app)->orderBy('batch','DESC')->first()->batch ?? 0;
    }

    public static function fetchAllByBatch($batch, $app)
    {
        return MigrationModel::where([
            ['batch', $batch],
            ['app', $app],
        ])->get()->toArray() ?? null;
    }

    public static function insert($fileName, $app, $batch)
    {
        return MigrationModel::create([
            'migration' => $fileName,
            'batch' => $batch + 1,
            'app' => $app,
        ]);
    }

    public static function is_exists($migration, $app)
    {
        return MigrationModel::where([
            ['migration', '=', $migration],
            ['app', '=', $app],
        ])->exists();
    }

    public static function delete($batch, $app)
    {
        return MigrationModel::where([
            ['batch', '=', $batch],
            ['app', '=', $app],
        ])->delete();
    }

}