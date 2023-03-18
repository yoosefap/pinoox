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
use pinoox\component\store\Config as ObjectPortal1;

/**
 * @method static ObjectPortal1 config(\pinoox\component\package\reference\PathReferenceInterface|string $packageName)
 * @method static bool exists(\pinoox\component\package\reference\PathReferenceInterface|string $packageName)
 * @method static AppEngine add($packageName, $path)
 * @method static bool supports(\pinoox\component\package\reference\PathReferenceInterface|string $packageName)
 * @method static string path(\pinoox\component\package\reference\PathReferenceInterface|string $packageName)
 * @method static \pinoox\component\package\engine\AppEngine object()
 *
 * @see \pinoox\component\package\engine\AppEngine
 */
class AppEngine extends Portal
{
	const file = 'app.php';

	public static function __register(): void
	{
		$pathApps = PINOOX_PATH . 'apps';
		$pathConfig = 'config' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'source.config.php';
		$default = Pinker::create(
		    PINOOX_PATH . 'pincore' . DIRECTORY_SEPARATOR . $pathConfig,
		    PINOOX_PATH . 'pincore' . DIRECTORY_SEPARATOR . Pinker::folder . DIRECTORY_SEPARATOR . $pathConfig,
		);
		self::__bind(\pinoox\component\package\engine\AppEngine::class)
		    ->setArguments([
		        $pathApps,
		        self::file,
		        Pinker::folder,
		        $default->pickup(),
		    ]);
	}


	/**
	 * Get the registered name of the component.
	 * @return string
	 */
	public static function __name(): string
	{
		return 'app.engine';
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
		return [
		    'add'
		];
	}
}
