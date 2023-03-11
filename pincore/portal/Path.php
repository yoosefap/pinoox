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
use pinoox\component\package\loader\ArrayLoader;
use pinoox\component\package\loader\ChainLoader;
use pinoox\component\package\loader\PackageLoader;
use pinoox\component\package\parser\AppNameParser;
use pinoox\component\source\Portal;

/**
 * @method static string get(\pinoox\component\package\AppReferenceInterface|string $path = '')
 * @method static ObjectPortal1 set($key, $value)
 * @method static string getBasePath($packageName)
 * @method static ds($path)
 * @method static \pinoox\component\helpers\Path object()
 *
 * @see \pinoox\component\helpers\Path
 */
class Path extends Portal
{
	public static function __register(): void
	{
		$loader = new ChainLoader([
		    new ArrayLoader([
		        'com_pinoox_welcome' => PINOOX_PATH . 'test',
		    ]),
		    new PackageLoader('apps', PINOOX_PATH),
		]);

		$parser = new AppNameParser();

		parent::__bind(ObjectPortal1::class)
		    ->setArgument('parser', $parser)
		    ->setArgument('loader', $loader)
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
