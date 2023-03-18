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

use pinoox\component\helpers\Path as ObjectPortal1;
use pinoox\component\package\parser\PathParser;
use pinoox\component\package\reference\PathReferenceInterface as ObjectPortal2;
use pinoox\component\source\Portal;

/**
 * @method static string get(\pinoox\component\package\reference\PathReferenceInterface|string $path = '')
 * @method static ObjectPortal1 set($key, $value)
 * @method static string|null app(?string $packageName = NULL)
 * @method static string ds(string $path)
 * @method static ObjectPortal2 parse(string $name)
 * @method static string prefix(\pinoox\component\package\reference\PathReferenceInterface|string $path, string $prefix)
 * @method static string prefixName(\pinoox\component\package\reference\PathReferenceInterface|string $path, string $prefix)
 * @method static reference(\pinoox\component\package\reference\PathReferenceInterface|string $path)
 * @method static ObjectPortal2 prefixReference(\pinoox\component\package\reference\PathReferenceInterface|string $path, string $prefix)
 * @method static \pinoox\component\helpers\Path object()
 *
 * @see \pinoox\component\helpers\Path
 */
class Path extends Portal
{
	public static function __register(): void
	{
		$parser = new PathParser('com_pinoox_test');
		parent::__bind(ObjectPortal1::class)
		    ->setArgument('parser', $parser)
		    ->setArgument('appEngine', AppEngine::object())
		    ->setArgument('packageName', 'com_pinoox_test')
		    ->setArgument('basePath', PINOOX_PATH);
	}


	/**
	 * Get the registered name of the component.
	 * @return string
	 */
	public static function __name(): string
	{
		return 'path';
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
