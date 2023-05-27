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

use pinoox\component\package\reference\PathReference;
use pinoox\component\package\reference\ReferenceInterface;
use pinoox\component\source\Portal;
use pinoox\component\store\baker\FileHandler;
use pinoox\component\store\baker\Pinker as ObjectPortal1;

/**
 * @method static array build($data, array $info = [])
 * @method static \pinoox\component\store\baker\Pinker object()
 *
 * @see \pinoox\component\store\baker\Pinker
 */
class Pinker extends Portal
{
	const folder = 'pinker';

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
	 * get pinker by file
	 *
	 * @param string|ReferenceInterface $fileName
	 * @return ObjectPortal1
	 */
	public static function file(string|ReferenceInterface $fileName): ObjectPortal1
	{
		$ref = Path::reference($fileName);
		$pathMain = $ref->getPackageName() === '~' ? 'pincore/' . $ref->getPath() : $ref->getPath();
		$pathBaked = $ref->getPackageName() === '~' ? 'pincore/' . self::folder . '/' . $ref->getPath() : self::folder . '/' . $ref->getPath();

		$ref = PathReference::create(
		    $ref->getPackageName(),
		    $pathMain,
		);

		$mainFile = Path::get($ref);
		$mainFile = is_file($mainFile) ? $mainFile : '';

		$ref = PathReference::create(
		    $ref->getPackageName(),
		    $pathBaked,
		);

		$bakedFile = Path::get($ref);

		return new ObjectPortal1($mainFile, $bakedFile, new FileHandler());
	}


	/**
	 * get pinker by path
	 *
	 * @param string $file
	 * @param string|null $basePath
	 * @return ObjectPortal1
	 */
	public static function path(string $file, ?string $basePath = null): ObjectPortal1
	{
		$basePath = !empty($basePath) ? $basePath . '/' : '';

		$mainFile = self::ds($basePath . $file);
		$bakedFile = self::ds($basePath . Pinker::folder . '/' . $file);
		return new ObjectPortal1($mainFile, $bakedFile, new FileHandler());
	}


	public static function ds(string $path): string
	{
		return str_replace(['/', '\\', '>'], DIRECTORY_SEPARATOR, $path);
	}


	/**
	 * Get include method names .
	 * @return string[]
	 */
	public static function __include(): array
	{
		return ['file', 'create', 'build'];
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
