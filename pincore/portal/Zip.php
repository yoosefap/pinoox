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

use pinoox\component\source\Portal;

/**
 * @method static array folders($zippedFile, $isJustCurrent = false, $dir = NULL)
 * @method static ?array info($zippedFile, $isJustCurrent = false, $dir = NULL)
 * @method static array files($zippedFile, $isJustCurrent = false, $dir = NULL)
 * @method static archive($source, $zipName = NULL, $overwrite = false, $no_file = [], $ext = [], $ext_action = 'out')
 * @method static Zip addEntries($filename)
 * @method static Zip entries($filenames)
 * @method static bool extract($zippedFile, $dir)
 * @method static bool remove($zippedFile, $path)
 * @method static \pinoox\component\Zip object()
 *
 * @see \pinoox\component\Zip
 */
class Zip extends Portal
{
	public static function __register(): void
	{
		self::__bind(\pinoox\component\Zip::class);
	}


	/**
	 * Get the registered name of the component.
	 * @return string
	 */
	public static function __name(): string
	{
		return 'zip';
	}


	/**
	 * Get method names for callback object.
	 * @return string[]
	 */
	public static function __callback(): array
	{
		return [
			'addEntries',
			'entries'
		];
	}
}
