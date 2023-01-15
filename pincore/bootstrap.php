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
define('PINOOX_VENDOR_PATH', PINOOX_PATH . 'vendor' . DIRECTORY_SEPARATOR);
define('PINOOX_CORE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('PINOOX_FUNCTIONS_PATH', PINOOX_CORE_PATH . 'functions' . DIRECTORY_SEPARATOR);
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
$composer = require PINOOX_VENDOR_PATH . 'autoload.php';
$loader = new ConstructStatic\Loader($composer);

/*
|--------------------------------------------------------------------------
| Register Pinoox Loader
|--------------------------------------------------------------------------
*/
require PINOOX_FUNCTIONS_PATH . 'base.php';
require PINOOX_FUNCTIONS_PATH . 'router' . DIRECTORY_SEPARATOR . 'routes.php';

Loader::boot();
