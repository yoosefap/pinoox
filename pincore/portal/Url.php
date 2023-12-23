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

use pinoox\component\Helpers\Url as ObjectPortal1;
use pinoox\component\Path\parser\UrlParser;
use pinoox\component\source\Portal;
use pinoox\portal\app\AppEngine;

/**
 * @method static ObjectPortal1 set($key, $value)
 * @method static string|null app(?string $packageName = NULL)
 * @method static string replaceToSlash(string $url, array|string $search = array (  0 => '\\',  1 => '>',))
 * @method static string prefix(\pinoox\component\Path\reference\ReferenceInterface|string $url, string $prefix)
 * @method static string get(\pinoox\component\Path\reference\ReferenceInterface|string $url = '')
 * @method static string prefixName(\pinoox\component\Path\reference\ReferenceInterface|string $url, string $prefix)
 * @method static \pinoox\component\Path\parser\UrlParser ___parser()
 * @method static \pinoox\component\Helpers\Url ___()
 *
 * @see \pinoox\component\Helpers\Url
 */
class Url extends Portal
{
	public static function __register(): void
	{
		self::__bind(UrlParser::class, 'parser')
		    ->setArguments(['com_pinoox_test']);

		self::__bind(ObjectPortal1::class)
		    ->setArgument('parser', self::__ref('parser'))
		    ->setArgument('appEngine', AppEngine::__instance())
		    ->setArgument('baseUrl', null);
	}


	/**
	 * Get the registered name of the component.
	 * @return string
	 */
	public static function __name(): string
	{
		return 'url';
	}


	/**
	 * Get exclude method names .
	 * @return string[]
	 */
	public static function __exclude(): array
	{
		return [];
	}


	/**
	 * Get method names for callback object.
	 * @return string[]
	 */
	public static function __callback(): array
	{
		return [];
	}
}
