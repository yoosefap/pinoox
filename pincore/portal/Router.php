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
use pinoox\component\router\Collection as ObjectPortal1;
use pinoox\component\source\Portal;

/**
 * @method static Router add(array|string $path, \Closure|array|string $action = '', string $name = '', array|string $methods = [], array $defaults = [], array $filters = [])
 * @method static mixed buildAction(mixed $action, ?int $indexCollection = NULL)
 * @method static mixed getAction(string $name)
 * @method static Router collection(string $path = '', array|callable|null|\pinoox\component\router\Router|string $routes = NULL, mixed $controller = NULL, array|string $methods = [], \Closure|array|string $action = '', $defaults = [], array $filters = [], string $prefixName = '')
 * @method static Router action(string $name, \Closure|array|string $action)
 * @method static ObjectPortal1 currentCollection()
 * @method static ?pinoox\component\router\Collection getCollection($index = 0)
 * @method static ObjectPortal1 getMainCollection()
 * @method static string path($name, $params = [])
 * @method static Router get(array|string $path, \Closure|array|string $action = '', string $name = '', array $defaults = [], array $filters = [])
 * @method static Router post(array|string $path, \Closure|array|string $action = '', string $name = '', array $defaults = [], array $filters = [])
 * @method static Router put(array|string $path, \Closure|array|string $action = '', string $name = '', array $defaults = [], array $filters = [])
 * @method static Router patch(array|string $path, \Closure|array|string $action = '', string $name = '', array $defaults = [], array $filters = [])
 * @method static Router delete(array|string $path, \Closure|array|string $action = '', string $name = '', array $defaults = [], array $filters = [])
 * @method static Router options(array|string $path, \Closure|array|string $action = '', string $name = '', array $defaults = [], array $filters = [])
 * @method static Router head(array|string $path, \Closure|array|string $action = '', string $name = '', array $defaults = [], array $filters = [])
 * @method static Router purge(array|string $path, \Closure|array|string $action = '', string $name = '', array $defaults = [], array $filters = [])
 * @method static Router trace(array|string $path, \Closure|array|string $action = '', string $name = '', array $defaults = [], array $filters = [])
 * @method static Router connect(array|string $path, \Closure|array|string $action = '', string $name = '', array $defaults = [], array $filters = [])
 * @method static Router test(array|string $path, \Closure|array|string $action = '', string $name = '', array|string $methods = [], array $defaults = [], array $filters = [])
 * @method static \pinoox\component\router\Router object()
 *
 * @see \pinoox\component\router\Router
 */
class Router extends Portal
{
    public static function __register(): void
    {
        $routes = App::get('router.routes');
        $path = App::path();

        self::__bind(\pinoox\component\router\Router::class)
            ->addMethodCall('collection',[
                $path,
                $routes
        ]);
    }

    /**
     * Get the registered name of the component.
     * @return string
     */
    public static function __name(): string
    {
        return 'router';
    }


    public static function __replace(): array
    {
        return [
            'get|post|put|patch|delete|options|head|purge|trace|connect' => function (
                array|string $path,
                \Closure|array|string $action = '',
                string $name = '',
                array $defaults = [],
                array $filters = []
            ): Router {
                return self::add($path, $action, $name, self::$__method, $defaults, $filters);
            },
        ];
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
            'add',
            'collection',
            'action'
        ];
    }
}
