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

namespace pinoox\app\com_pinoox_manager\service\app;

use pinoox\app\com_pinoox_manager\component\Wizard;
use pinoox\component\worker\Config;
use pinoox\component\Dir;
use pinoox\component\File;
use pinoox\component\interfaces\ServiceInterface;
use pinoox\component\User;
use pinoox\model\PincoreModel;
use pinoox\model\UserModel;

class UpdateService implements ServiceInterface
{

    public function _run()
    {
        Config::init('options')
            ->delete('pinoox_auth')
            ->save();

        $dir = Dir::path('pinupdate/', 'com_pinoox_manager');
        if (!is_dir($dir))
            return;

        $pinoox_version_code = Config::init('~pinoox')->get('version_code');
        $files = File::get_files_by_pattern($dir, '*.db');

        foreach ($files as $file) {
            $version_code = File::name($file);
            if ($pinoox_version_code <= $version_code) {
                $this->runQuery($file);
            }
        }

        File::remove($dir);
    }

    private static function runQuery($appDB)
    {
        if (is_file($appDB)) {
            $packageName = 'com_pinoox_manager';

            $prefix = Config::init('~database')->get('prefix');
            $query = file_get_contents($appDB);
            $query = str_replace('{dbprefix}', $prefix . $packageName . '_', $query);
            $queryArr = explode(';', $query);

            PincoreModel::$db->startTransaction();
            foreach ($queryArr as $q) {
                if (empty($q)) continue;
                PincoreModel::$db->mysqli()->query($q);
            }

            PincoreModel::$db->commit();

            File::remove_file($appDB);

            return true;
        }
        return false;
    }

    public function _stop()
    {
    }
}
