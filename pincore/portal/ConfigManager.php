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

use pinoox\component\package\reference\ReferenceInterface;
use pinoox\component\source\Portal;
use pinoox\component\store\ConfigManager as ObjectPortal1;
use pinoox\component\store\data\DataArray;
use pinoox\component\store\strategy\ConfigStrategyInterface;
use pinoox\component\store\strategy\FileConfigStrategy;

/**
 * @method static ConfigManager save()
 * @method static mixed get(?string $key = NULL)
 * @method static ObjectPortal1 add(string $key, mixed $value)
 * @method static ObjectPortal1 set(string $key, mixed $value)
 * @method static ObjectPortal1 remove(string $key)
 * @method static ObjectPortal1 merge(array $array)
 * @method static ObjectPortal1 reset()
 * @method static setLinear(string $key, string $target, mixed $value)
 * @method static getLinear(string $key, string $target)
 * @method static \pinoox\component\store\ConfigManager object()
 *
 * @see \pinoox\component\store\ConfigManager
 */
class ConfigManager extends Portal
{
    const folder = 'config';
    private $strategy;

    public static function __register(): void
    {
        self::__bind(ObjectPortal1::class)->setArguments([Pinker::__ref()]);
    }


    /**
     * Get the registered name of the component.
     * @return string
     */
    public static function __name(): string
    {
        return 'config.manager';
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
     * Set file for pinoox baker
     *
     * @param string|ReferenceInterface $fileName
     * @return ObjectPortal1
     */
    public static function name(string|ReferenceInterface $fileName): ObjectPortal1
    {
        return self::initFileConfig($fileName);
    }

    private static function initFileConfig(string $fileName): ObjectPortal1
    {
        $fileName = $fileName . '.config.php';
        $ref = Path::prefixReference($fileName, self::folder);
        $pinker = Pinker::file($ref);
        $array = new DataArray($pinker->pickup());
        return new ObjectPortal1(new FileConfigStrategy($pinker, $array));
    }

    /**
     * Get method names for callback object.
     * @return string[]
     */
    public static function __callback(): array
    {
        return [
            'save'
        ];
    }
}
