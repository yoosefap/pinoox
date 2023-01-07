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

use pinoox\component\kernel\Loader;

define('PINOOX_DEFAULT_LANG', 'en');
define('PINOOX_PATH', realpath(__DIR__ . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);
define('PINOOX_CORE_PATH', PINOOX_PATH . 'pincore' . DIRECTORY_SEPARATOR);
define('PINOOX_BOOT_PATH', PINOOX_CORE_PATH . 'boot' . DIRECTORY_SEPARATOR);
define('PINOOX_MODEL_PATH', PINOOX_CORE_PATH . 'model' . DIRECTORY_SEPARATOR);
define('PINOOX_COMPONENT_PATH', PINOOX_CORE_PATH . 'component' . DIRECTORY_SEPARATOR);
define('PINOOX_SERVICE_PATH', PINOOX_CORE_PATH . 'service' . DIRECTORY_SEPARATOR);
define('PINOOX_CONFIG_PATH', PINOOX_CORE_PATH . 'config' . DIRECTORY_SEPARATOR);
define('PINOOX_LANG_PATH', PINOOX_CORE_PATH . 'lang' . DIRECTORY_SEPARATOR);
define('PINOOX_PATH_THUMB', 'thumbs/{name}_{size}.{ext}');
/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/
$composer = require PINOOX_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$loader = new ConstructStatic\Loader($composer);

/*
|--------------------------------------------------------------------------
| Register Pinoox Loader
|--------------------------------------------------------------------------
*/
include __DIR__ . DIRECTORY_SEPARATOR . 'boot' . DIRECTORY_SEPARATOR . 'functions.php';
include __DIR__ . DIRECTORY_SEPARATOR . 'boot' . DIRECTORY_SEPARATOR . 'routes.php';

Loader::boot();
