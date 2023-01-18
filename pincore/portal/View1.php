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
use pinoox\component\template\View as ObjectPortal1;


/**
 * @method static setView(array|null|string $folders = NULL, ?string $pathTheme = NULL)
 * @method static string renderFile(string $name, array $parameters = [])
 * @method static bool existsFile(string $name)
 * @method static bool exists(string $name)
 * @method static array getAll()
 * @method static mixed get(int|string $index)
 * @method static void set(string $name, mixed $value)
 * @method static array engines()
 * @method static string render(string $name, array $parameters = [])
 * @method static ObjectPortal1 ready(string $name = '', array $parameters = [])
 * @method static string getContentReady()
 */
class View1 extends Portal
{
	/**
	 * Get the registered name of the component.
	 * @return string
	 */
	public static function __name(): string
	{
		return 'view';
	}
}
