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

use pinoox\component\Dir;
use pinoox\component\package\App;
use pinoox\component\source\Portal;
use pinoox\component\template\View as ObjectPortal1;

/**
 * @method static setView(array|null|string $folders = NULL, ?string $pathTheme = NULL)
 * @method static string renderFile(string $name, array $parameters = [])
 * @method static bool existsFile(string $name)
 * @method static bool exists(string $name)
 * @method static array getAll()
 * @method static mixed get(int|string $index)
 * @method static View set(string $name, mixed $value)
 * @method static array engines()
 * @method static string render(string $name, array $parameters = [])
 * @method static View ready(string $name = '', array $parameters = [])
 * @method static string getContentReady()
 * @method static \pinoox\component\template\View object()
 *
 * @see \pinoox\component\template\View
 */
class View extends Portal
{
    public static function __register(): void
    {
        // theme names
        $folders = App::get('theme');

        // base path
        $pathTheme = Path::get(App::get('path-theme'));

        self::__bind(ObjectPortal1::class)->setArguments([
            $folders,
            $pathTheme
        ]);
    }

    /**
     * Get the registered name of the component.
     * @return string
     */
    public static function __name(): string
    {
        return 'view';
    }

    /**
     * Get exclude method names.
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
            'set',
            'ready'
        ];
    }
}
