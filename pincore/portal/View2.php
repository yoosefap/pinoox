<?php

/**
 * ***  *  *     *  ****  ****  *    *
 *   *  *  * *   *  *  *  *  *   *  *
 * ***  *  *  *  *  *  *  *  *    *
 *      *  *   * *  *  *  *  *   *  *
 *      *  *    **  ****  ****  *    *
 *
 * @author   Pinoox
 * @link https://www.pinoox.com
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace pinoox\portal;

use pinoox\component\Upload;
use pinoox\component\source\Portal;

/**
 * @method static file($file)
 * @method static folder($folder)
 * @method static init($file = NULL, $folder = NULL)
 * @method static getInstance($file = NULL, $folder = NULL)
 * @method static stopUpload($error = NULL, $is_arr = false, $filename = NULL)
 * @method static create($file, $folder)
 * @method static type($type = 'form')
 * @method static copy()
 * @method static move()
 * @method static base64()
 * @method static limit($limit)
 * @method static transaction($isTransaction = true)
 * @method static resize($dir, $w = 100, $h = 100, $fix = false)
 * @method static thumb($size = 100, $path = 'thumbs/{name}_{size}.{ext}')
 * @method static watermark($logo, $h = 1, $w = 1, $div = 2)
 * @method static converterImage($typeConvert = 'png', $is_old_delete = false, $new_dir = NULL)
 * @method static defaultSize($size)
 * @method static sizeUnit($typeSize)
 * @method static allowedTypes($exts, $sizeDefault = 0)
 * @method static notAllowedTypes($exts)
 * @method static changeName($convert, $isName = false, $ext = '', $pre = '')
 * @method static error($nested = false)
 * @method static result()
 * @method static finish($single = false)
 * @method static process($single = false, $isObj = false)
 * @method static beforeUpload(Closure $func)
 * @method static afterUpload(Closure $func)
 * @method static commit()
 */
class View2 extends Portal
{
    public static function __register(): void
    {
        dump('test');
        pincore()->register('test', Upload::class);
    }


    /**
     * Get the registered name of the component.
     * @return string
     */
    public static function __name(): string
    {
        return 'test';
    }

}