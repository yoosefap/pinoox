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


namespace pinoox\app\com_pinoox_manager\component;


use pinoox\component\Cache;
use pinoox\component\package\AppBuilder;
use pinoox\component\store\Config;
use pinoox\component\Dir;
use pinoox\component\File;
use pinoox\component\Lang;
use pinoox\component\package\App;
use pinoox\component\Service;
use pinoox\component\Url;
use pinoox\component\User;
use pinoox\component\Zip;
use pinoox\model\PincoreModel;
use pinoox\model\SessionModel;
use pinoox\model\TokenModel;
use pinoox\model\UserModel;

class Wizard
{
    private static $isApp = false;
    private static $message = null;

    public static function installApp($pinFile)
    {
        $data = self::pullDataPackage($pinFile);

        if (!self::isValidNamePackage($data['package_name']))
            return false;

        if (!self::checkVersion($data))
            return false;

        $appPath = path('~apps/' . $data['package_name'] . '/');
        Zip::extract($pinFile, $appPath);

        //check database
        $appDB = path('~apps/' . $data['package_name'] . '/app.db');

        self::runQuery($appDB, $data['package_name']);
        self::changeLang($data['package_name']);
        self::runService($data['package_name'], 'install');
        App::meeting('com_pinoox_manager', function () use ($pinFile) {
            self::deletePackageFile($pinFile);
        });

        return true;
    }

    public static function pullDataPackage($pinFile)
    {
        $filename = File::fullname($pinFile);
        $size = File::size($pinFile);
        $name = File::name($pinFile);
        $dir = File::dir($pinFile) . DIRECTORY_SEPARATOR . $name;
        $configFile = $dir . DIRECTORY_SEPARATOR . 'app.php';

        if (!is_file($configFile)) {
            Zip::addEntries('app.php');
            Zip::extract($pinFile, $dir);
        }

        $app = AppBuilder::file($configFile);

        $iconPath = $app->get('icon');

        $icon = Url::file('resources/default.png');
        if (!empty($iconPath)) {
            $iconFile = Dir::path($dir . '>' . $iconPath);
            if (!is_file($iconFile)) {
                Zip::addEntries($iconPath);
                Zip::extract($pinFile, $dir);
            }

            if (is_file($iconFile))
                $icon = Url::file($dir . '>' . $iconPath);
        }

        return [
            'type' => 'app',
            'filename' => $filename,
            'package_name' => $app->get('package-name'),
            'app' => $app->get('package-name'),
            'name' => $app->get('name'),
            'description' => $app->get('description'),
            'version' => $app->get('version-name'),
            'version_code' => $app->get('version-code'),
            'developer' => $app->get('developer'),
            'path_icon' => $app->get('icon'),
            'icon' => $icon,
            'size' => File::print_size($size, 1),
        ];
    }

    public static function isValidNamePackage($packageName)
    {
        if (!empty($packageName)) {
            $parts = explode('_', $packageName);
            return count($parts) >= 2;
        }
        return false;
    }

    public static function checkVersion($data)
    {
        $packageName = $data['package_name'];
        $versionCode = @$data['version_code'];

        if (!App::exists($packageName))
            return true;

        $versionCodeApp = AppBuilder::init($packageName)->get('version-code');

        if ($versionCodeApp == $versionCode) {
            self::$message = Lang::get('manager.version_already_installed');
            return false;
        } else if ($versionCodeApp > $versionCode) {
            self::$message = Lang::get('manager.newer_version_installed');
            return false;
        }

        return true;
    }

    public static function runQuery($appDB, $packageName, $isRemoveFile = true, $isCopyUser = true)
    {
        if (is_file($appDB)) {
            $prefix = Config::init('~database')->get('prefix');
            $query = file_get_contents($appDB);
            $query = str_replace('{dbprefix}', $prefix . $packageName . '_', $query);
            $queryArr = explode(';', $query);

            PincoreModel::$db->startTransaction();
            foreach ($queryArr as $q) {
                if (empty($q)) continue;
                PincoreModel::$db->mysqli()->query($q);
            }

            //copy new user
            if ($isCopyUser)
                UserModel::copy(User::get('user_id'), $packageName);

            PincoreModel::$db->commit();

            if ($isRemoveFile)
                File::remove_file($appDB);

            return true;
        }
        return false;
    }

    public static function changeLang($packageName)
    {
        $lang = Lang::current();
        if (!Lang::exists($lang, $packageName))
            return false;
        AppBuilder::init($packageName)->set('lang', $lang)->save();
        return true;
    }

    private static function runService($packageName, $state = 'install')
    {
        App::meeting($packageName, function () use ($packageName, $state) {
            Cache::app($packageName);
            Service::app($packageName);
            Service::run('app>' . $state);
        });
    }

    public static function deletePackageFile($pinFile)
    {
        $name = File::name($pinFile);
        $dir = File::dir($pinFile) . DIRECTORY_SEPARATOR . $name;
        File::remove_file($pinFile);
        File::remove($dir);
    }

    public static function updateApp($pinFile)
    {
        $data = self::pullDataPackage($pinFile);

        if (!self::isValidNamePackage($data['package_name'])) {
            self::deletePackageFile($pinFile);
            return false;
        }

        if (!self::checkVersion($data))
            return false;

        Zip::remove($pinFile, [
            'pinker/',
        ]);

        $appPath = path('~apps/' . $data['package_name'] . '/');

        Zip::extract($pinFile, $appPath);
        File::remove_file($pinFile);

        AppBuilder::init($data['package_name'])
            ->set('version-code', $data['version_code'])
            ->set('version-name', $data['version'])
            ->set('name', $data['name'])
            ->set('developer', $data['developer'])
            ->set('description', $data['description'])
            ->set('icon', $data['path_icon'])
            ->save();
        self::runService($data['package_name'], 'update');

        App::meeting('com_pinoox_manager', function () use ($pinFile) {
            self::deletePackageFile($pinFile);
        });

        return true;

    }

    public static function getMessage()
    {
        $message = self::$message;
        self::$message = null;
        return $message;
    }

    public static function deleteApp($packageName)
    {
        $appPath = path('~apps/' . $packageName);
        File::remove($appPath);

        //remove route
        self::removeRoutes($packageName);

        //remove database
        self::removeDatabase($packageName);

        self::runService($packageName, 'delete');
    }

    private static function removeRoutes($packageName)
    {
        $routes = Config::init('~app')->get();
        foreach ($routes as $alias => $package) {
            if ($package == $packageName && $alias != '*') {
                unset($routes[$alias]);
            }
        }
        Config::init('~app')
            ->data($routes)
            ->save();
    }

    private static function removeDatabase($packageName)
    {
        PincoreModel::startTransaction();

        $tables = PincoreModel::getTables($packageName);
        $tables = implode(',', $tables);
        PincoreModel::$db->rawQuery("SET FOREIGN_KEY_CHECKS = 0");

        //delete all tables
        if (!empty($tables))
            PincoreModel::$db->rawQuery("DROP TABLE IF EXISTS " . $tables);

        //delete all rows
        UserModel::delete_by_app($packageName);
        TokenModel::delete_by_app($packageName);
        SessionModel::delete_by_app($packageName);

        PincoreModel::$db->rawQuery("SET FOREIGN_KEY_CHECKS = 1");
        PincoreModel::commit();
    }

    public static function updateCore($file)
    {
        Zip::extract($file, path('~'));
        File::remove_file($file);
        Cache::clean('version');
        Cache::get('version');
        Config::bake('~pinoox');
        Service::run('~core>update');

        Cache::app('com_pinoox_manager');
        Service::app('com_pinoox_manager');
        Service::run('app>update');
    }

    public static function app_state($packageName)
    {
        if (self::is_installed($packageName))
            $state = 'installed';
        else if (self::is_downloaded($packageName))
            $state = 'install';
        else
            $state = 'download';

        return $state;
    }

    public static function is_installed($packageName)
    {
        return App::exists($packageName);
    }

    public static function is_downloaded($packageName)
    {
        $file = Dir::path('downloads>apps>' . $packageName . '.pin');
        return (!empty($file) && file_exists($file));
    }

    public static function get_downloaded($packageName)
    {
        return Dir::path('downloads>apps>' . $packageName . '.pin');
    }

    public static function template_state($packageName, $uid)
    {
        if (self::is_installed_template($packageName, $uid))
            $state = 'installed';
        else if (self::is_downloaded_template($uid))
            $state = 'install';
        else
            $state = 'download';

        return $state;
    }

    public static function is_installed_template($packageName, $uid)
    {
        $file = Dir::path("~apps>$packageName>theme>$uid");
        return (!empty($file) && file_exists($file));
    }

    public static function is_downloaded_template($uid)
    {
        $file = Dir::path("downloads>templates>$uid.pin");
        return (!empty($file) && file_exists($file));
    }

    public static function get_downloaded_template($uid)
    {
        return Dir::path("downloads>templates>$uid.pin");
    }

    public static function installTemplate($file, $packageName, $meta)
    {
        if (Zip::extract($file, path("~apps>$packageName>theme>" . $meta['name']))) {
            File::remove_file($file);
            return true;
        }

        return false;
    }

    public static function deleteTemplate($packageName, $folderName)
    {
        $templatePath = path('~apps/' . $packageName . '>theme>' . $folderName);
        File::remove($templatePath);
    }

    public static function checkTemplateFolderName($packageName, $templateFolderName)
    {
        $file = path("~apps>$packageName>theme>" . $templateFolderName);
        return file_exists($file);
    }

    public static function pullTemplateMeta($pinFile)
    {
        $filename = File::fullname($pinFile);
        $size = File::size($pinFile);
        $name = File::name($pinFile);
        $dir = File::dir($pinFile) . DIRECTORY_SEPARATOR . $name;
        $metaFile = $dir . DIRECTORY_SEPARATOR . 'meta.json';

        if (!is_file($metaFile)) {
            Zip::addEntries('meta.json');
            Zip::extract($pinFile, $dir);
        }

        $meta = json_decode(file_get_contents($metaFile), true);
        $coverPath = @$meta['cover'];

        $cover = Url::file('resources/theme.jpg');
        if (!empty($coverPath)) {
            $coverFile = Dir::path($dir . '>' . $coverPath);
            if (!is_file($coverFile)) {
                Zip::addEntries($coverPath);
                Zip::extract($pinFile, $dir);
            }

            if (is_file($coverFile))
                $cover = Url::file($dir . '>' . $coverPath);
        }

        if (empty($meta['title'])) {
            $title = null;
        } else if (empty($meta['title'][Lang::current()])) {
            $title = array_values($meta['title'])[0];
        } else {
            $title = $meta['title'][Lang::current()];
        }

        return [
            'type' => 'theme',
            'filename' => $filename,
            'template_name' => $title,
            'app' => @$meta['app'],
            'name' => @$meta['name'],
            'title' => @$meta['title'],
            'description' => @$meta['description'],
            'version' => @$meta['version'],
            'version_code' => @$meta['app_version'],
            'developer' => @$meta['developer'],
            'path_cover' => @$meta['cover'],
            'cover' => $cover,
            'size' => File::print_size($size, 1),
        ];
    }
}