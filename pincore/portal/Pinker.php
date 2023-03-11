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
use pinoox\component\store\Pinker as ObjectPortal1;

/**
 * @method static ObjectPortal1 data(mixed $data)
 * @method static ObjectPortal1 info(array $info)
 * @method static ?array getInfo(?string $key = NULL)
 * @method static ObjectPortal1 dumping(bool $status = true)
 * @method static ObjectPortal1 file(string $fileName)
 * @method static ObjectPortal1 bake()
 * @method static pickup()
 * @method static remove()
 * @method static array build($data, array $info = [])
 * @method static \pinoox\component\store\Pinker object()
 *
 * @see \pinoox\component\store\Pinker
 */
class Pinker extends Portal
{
	public static function __register(): void
	{
		self::__bind(ObjectPortal1::class);
	}


	/**
	 * Get the registered name of the component.
	 * @return string
	 */
	public static function __name(): string
	{
		return 'pinker';
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
