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

namespace pinoox\app\com_pinoox_manager\model;

use Illuminate\Database\Eloquent\Model;
use pinoox\app\com_pinoox_manager\component\Wizard;
use pinoox\component\app\AppProvider;
use pinoox\component\package\AppBuilder;
use pinoox\component\worker\Config;
use pinoox\component\Dir;
use pinoox\component\File;
use pinoox\component\package\App;
use pinoox\component\Router;
use pinoox\component\Url;

class AppModel extends Model
{
    /**
     * @param null|boolean $sysApp null: return all installed and system apps | true: return all system apps | false: return all installed app
     * @param bool $isCheckHidden
     * @param bool $isCheckRouter
     * @return array
     */
    public static function fetch_all($sysApp = null, $isCheckHidden = false, $isCheckRouter = false)
    {
        $path = Dir::path('~apps/');
        $folders = File::get_dir_folders($path);
        $icon_default = Url::file('resources/default.png');
        $app = App::package();

        $result = [];
        foreach ($folders as $folder) {
            $package_key = basename($folder);

            if (!Router::existApp($package_key))
                continue;
            $app = AppBuilder::init($package_key);

            $isEnable = $app->get('enable');
            if (!$isEnable)
                continue;

            $isHidden = $app->get('hidden');
            if ($isHidden)
                continue;

            $isRouter = $app->get('router.type');
            if ($isCheckRouter && !$isRouter)
                continue;

            if (!is_null($sysApp)) {
                $sysAppState = $app->get('sys-app');
                if ($sysApp && !$sysAppState) {
                    continue;
                } else if (!$sysApp && $sysAppState) {
                    continue;
                }
            }

            $result[$package_key] = [
                'package_name' => $package_key,
                'hidden' => $isHidden,
                'dock' => $app->get('dock'),
                'router' => $isRouter,
                'name' => $app->get('name'),
                'description' => $app->get('description'),
                'version' => $app->get('version-name'),
                'version_code' => $app->get('version-code'),
                'developer' => $app->get('developer'),
                'open' => $app->get('open'),
                'sys_app' => $app->get('sys-app'),
                'icon' => Url::check(Url::file($app->get('icon'), $package_key), $icon_default),
                'routes' => self::fetch_all_aliases_by_package_name($package_key)
            ];
        }
        return $result;
    }

    public static function fetch_all_aliases_by_package_name($packageName)
    {
        $routes = Config::init('~app')->get();
        $aliases = [];
        foreach ($routes as $alias => $package) {
            if ($package == $packageName) {
                $aliases[] = $alias;
            }
        }
        return $aliases;
    }

    public static function fetch_by_package_name($packageName)
    {
        $icon_default = Url::file('resources/default.png');
        $result = null;
        if (App::exists($packageName)) {

            $app = AppBuilder::init($packageName);
            $result = [
                'name' => $app->get('name'),
                'hidden' => $app->get('hidden'),
                'dock' => $app->get('dock'),
                'router' => $app->get('router'),
                'enable' => $app->get('enable'),
                'open' => $app->get('open'),
                'sys-app' => $app->get('sys-app'),
                'description' =>$app->get('description'),
                'version' =>$app->get('version-name'),
                'version_code' => $app->get('version-code'),
                'developer' => $app->get('developer'),
                'icon' => Url::check(Url::file(App::get('icon'), $packageName), $icon_default),
            ];
        }

        return $result;
    }

    public static function fetch_all_downloads()
    {
        $folders = File::get_dir_folders(Dir::path('downloads>apps'));
        if (!empty($folders) && isset($folders[0])) {
            $folder = $folders[0];
            $files = File::get_files_by_pattern($folder, '*.pin');
            $result = [];

            foreach ($files as $file)
            {
                $data = Wizard::pullDataPackage($file);
                $package_name = Config::init('market')->get($data['package_name']);
                if (!Wizard::isValidNamePackage($data['package_name']) || !$package_name)
                {
                    Wizard::deletePackageFile($file);
                    Config::init('market')
                        ->delete($data['package_name'])
                        ->save();
                    continue;
                }
                $data['market'] = Config::init('market')->get($data['package_name']);
                $result[] = $data;
            }

            return $result;
        }

    }


}