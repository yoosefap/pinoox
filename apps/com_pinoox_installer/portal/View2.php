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

namespace pinoox\app\com_pinoox_installer\portal;

use pinoox\component\Uploader;
use pinoox\component\source\Portal;
use pinoox\component\template\View as ObjectPortal1;
use pinoox\portal\View;

/**
 * @method static init($file = NULL, $folder = NULL)
 * @method static getInstance($file = NULL, $folder = NULL)
 * @method static isCommit()
 * @method static isInsert()
 * @method static getInsertId($isMulti = false)
 * @method static getId($isMulti = false)
 * @method static deleteFolder()
 * @method static popId()
 * @method static shiftId()
 * @method static popInsertId()
 * @method static shiftInsertId()
 * @method static getResultInsert($isMulti = false)
 * @method static getResultUpdate()
 * @method static getResultEdit($isMulti = false)
 * @method static insert($access = NULL, $group = NULL, $user_id = NULL)
 * @method static update($file_id, $access = NULL)
 * @method static edit($file_id, $access = NULL, $group = NULL, $user_id = NULL)
 * @method static removeDir($dir)
 * @method static removeRow($file_id)
 * @method static commit()
 * @method static actRemoveRow($row)
 * @method static file($file)
 * @method static folder($folder)
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
 */
class View2 extends Portal
{
	public static function __register(): void
	{
		container()->register('uploader1',Uploader::class);
	}


	/**
	 * Get the registered name of the component.
	 * @return string
	 */
	public static function __name(): string
	{
		return 'uploader1';
	}
}
