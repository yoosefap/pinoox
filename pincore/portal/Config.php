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

use pinoox\component\package\App;
use pinoox\component\source\Portal;
use pinoox\component\store\Config as ObjectPortal1;

/**
 * @method static ObjectPortal1 name(string $name)
 * @method static string getName()
 * @method static string getKey()
 * @method static ObjectPortal1 setLinear(string $pointer, ?string $key, mixed $value)
 * @method static mixed get(?string $value = NULL)
 * @method static array getInfo(?string $key = NULL)
 * @method static ObjectPortal1 set(string $key, mixed $value)
 * @method static ObjectPortal1 data(mixed $value)
 * @method static mixed getLinear(?string $pointer, ?string $key)
 * @method static ObjectPortal1 delete(string $key)
 * @method static ObjectPortal1 deleteLinear(string $pointer, ?string $key)
 * @method static ObjectPortal1 reset()
 * @method static ObjectPortal1 add(string $key, string $value)
 * @method static ObjectPortal1 save()
 * @method static \pinoox\component\store\Config object()
 *
 * @see \pinoox\component\store\Config
 */
class Config extends Portal
{
	/**
	 * Instance class
	 *
	 * @var \pinoox\component\store\Config
	 */
	private static ObjectPortal1 $obj;

	/** @var Config[] */
	private static array $objects;


	public static function __register(): void
	{
		self::__bind(ObjectPortal1::class)->setArguments(['~core']);
	}


	/**
	 * Get the registered name of the component.
	 * @return string
	 */
	public static function __name(): string
	{
		return 'config';
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
