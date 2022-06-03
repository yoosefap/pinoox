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

use pinoox\boot\Loader;

define('PINOOX_DEFAULT_LANG', 'en');
define('PINOOX_PATH', realpath(dirname(__FILE__) .DIRECTORY_SEPARATOR. '..') . DIRECTORY_SEPARATOR);

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
$composer = require PINOOX_PATH.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
$loader = new ConstructStatic\Loader($composer);

/*
|--------------------------------------------------------------------------
| Register Pinoox Loader
|--------------------------------------------------------------------------
*/
require_once(__DIR__.DIRECTORY_SEPARATOR.'boot' . DIRECTORY_SEPARATOR . 'functions.php');

Loader::boot();