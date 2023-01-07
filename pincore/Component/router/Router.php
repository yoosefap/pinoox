<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */


namespace pinoox\component\router;

use pinoox\component\Dir;
use pinoox\component\kernel\Container;
use pinoox\component\package\App;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Closure;
use Exception;

/**
 *
 * @method static void get(array|string $path, array|string|Closure $action = '', string $name = null, array $defaults = [], array $filters = [])
 * @method static void post(array|string $path, array|string|Closure $action = '', string $name = null, array $defaults = [], array $filters = [])
 * @method static void put(array|string $path, array|string|Closure $action = '', string $name = null, array $defaults = [], array $filters = [])
 * @method static void patch(array|string $path, array|string|Closure $action = '', string $name = null, array $defaults = [], array $filters = [])
 * @method static void delete(array|string $path, array|string|Closure $action = '', string $name = null, array $defaults = [], array $filters = [])
 * @method static void options(array|string $path, array|string|Closure $action = '', string $name = null, array $defaults = [], array $filters = [])
 * @method static void head(array|string $path, array|string|Closure $action = '', string $name = null, array $defaults = [], array $filters = [])
 * @method static void purge(array|string $path, array|string|Closure $action = '', string $name = null, array $defaults = [], array $filters = [])
 * @method static void trace(array|string $path, array|string|Closure $action = '', string $name = null, array $defaults = [], array $filters = [])
 * @method static void connect(array|string $path, array|string|Closure $action = '', string $name = null, array $defaults = [], array $filters = [])
 */
class Router
{
    /**
     * current collection index
     * @var int
     */
    private static int $current = -1;

    /**
     * @var Collection[]
     */
    private static array $collections = [];

    /**
     * @var array
     */
    private static array $actions = [];

    public static function __constructStatic()
    {
        self::collection(routes: function () {
        });
    }

    /**
     * init Router
     */
    public static function init()
    {
        self::reset();
        $routePath = App::get('router.routes');
        self::collection(path: App::path(), routes: $routePath);
    }

    /**
     * reset Router
     */
    public static function reset()
    {
        self::$collections = [];
        self::$actions = [];
        self::$current = -1;
    }

    /**
     * add route
     *
     * @param string|array $path
     * @param array|string|Closure $action
     * @param string $name
     * @param string|array $methods
     * @param array $defaults
     * @param array $filters
     */
    public static function add(string|array $path, array|string|Closure $action = '', string $name = '', string|array $methods = [], array $defaults = [], array $filters = []): void
    {
        if (is_array($path)) {
            foreach ($path as $routeName => $p) {
                $routeName = is_string($routeName) ? $name . $routeName : $name . Route::generateName();
                $path = isset($p['path']) ? $p['path'] : $p;
                $action = isset($p['action']) ? $p['action'] : $action;
                $methods = isset($p['methods']) ? $p['methods'] : $methods;
                $defaults = isset($p['defaults']) ? $p['defaults'] : $defaults;
                $filters = isset($p['filters']) ? $p['filters'] : $filters;
                self::add($path, $action, $routeName, $methods, $defaults, $filters);
            }
            return;
        }

        $route = new Route(
            collection: self::currentCollection(),
            path: $path,
            action: $action,
            name: $name,
            methods: $methods,
            defaults: $defaults,
            filters: $filters,
        );

        self::currentRoutes()->add($route->getName(), $route->get(), $route->countAll());
    }

    /**
     * call static for adding route by methods
     *
     * @param string $method
     * @param array $arguments
     */
    public static function __callStatic(string $method, array $arguments)
    {
        if (RouteMethod::valid($method)) {
            self::add(@$arguments[0], @$arguments[1], @$arguments[2], $method, @$arguments[3], @$arguments[4]);
        } else {
            throw new BadMethodCallException('"' . $method . '" static method is not found in ' . __CLASS__ . ' class');
        }
    }

    /**
     * build action
     *
     * @param mixed $action
     * @param int|null $indexCollection
     * @return mixed
     */
    public static function buildAction(mixed $action, ?int $indexCollection = null): mixed
    {
        $collection = isset(self::$collections[$indexCollection]) ? self::$collections[$indexCollection] : self::currentCollection();
        return $collection->buildAction($action);
    }

    /**
     * get action
     *
     * @param string $name
     * @return mixed
     */
    public static function getAction(string $name): mixed
    {
        $name = self::buildNameAction($name, false);
        if (isset(self::$actions[$name]))
            return self::$actions[$name];

        return false;
    }

    /**
     * add collection
     *
     * @param string $path
     * @param mixed|null $controller
     * @param array|string $methods
     * @param array|string|Closure $action
     * @param string|array|callable|null $routes
     * @param array $defaults
     * @param array $filters
     * @param string $prefixName
     */
    public static function collection(string $path = '', mixed $controller = null, array|string $methods = [], array|string|Closure $action = '', string|array|callable|null $routes = null, $defaults = [], array $filters = [], string $prefixName = ''): void
    {
        $cast = self::$current;
        $prefixName = self::buildPrefixNameCollection($prefixName);
        $defaults = self::buildDefaultsCollection($defaults);
        $filters = self::buildFiltersCollection($filters);
        $controller = self::buildControllerCollection($controller);
        $prefixPath = self::buildPrefixPathCollection($path);


        self::$current = count(self::$collections);
        self::$collections[self::$current] = new Collection(
            path: $path,
            prefixPath: $prefixPath,
            cast: $cast,
            controller: $controller,
            methods: $methods,
            action: $action,
            defaults: $defaults,
            filters: $filters,
            name: $prefixName,
        );

        self::callRoutes($routes);

        $collection = self::currentCollection();
        if ($collection->cast !== -1) {
            self::$current = $collection->cast;
            $routeCollection = $collection->get();
            if (!empty($collection->path))
                $routeCollection->addPrefix($collection->path);
            self::currentRoutes()->addCollection($routeCollection);
        }
    }

    /**
     * run a routes collection
     * @param $routes
     */
    private static function callRoutes($routes): void
    {
        if (is_callable($routes)) {
            $routes();
        } else {
            self::loadFiles($routes);
        }
    }

    /**
     * load route file
     *
     * @param string|array $routes
     */
    private static function loadFiles(string|array $routes): void
    {
        if (is_string($routes)) {
            $routes = Dir::path($routes);
            if (is_file($routes))
                include $routes;
        } else if (is_array($routes)) {
            foreach ($routes as $route) {
                self::loadFiles($route);
            }
        }
    }

    /**
     * add action
     *
     * @param string $name
     * @param array|string|Closure $action
     */
    public static function action(string $name, array|string|Closure $action): void
    {
        $name = self::buildNameAction($name);
        self::$actions[$name] = self::currentCollection()->buildAction($action);
    }

    /**
     * get current routes
     *
     * @return RouteCollection
     */
    private static function currentRoutes(): RouteCollection
    {
        return self::currentCollection()->routes;
    }

    /**
     * get current Collection
     *
     * @return Collection
     */
    public static function currentCollection(): Collection
    {
        return self::$collections[self::$current];
    }

    /**
     * create name for action
     *
     * @param string $name
     * @param bool $isPrefix
     * @return string
     */
    private static function buildNameAction(string $name, bool $isPrefix = true): string
    {
        $prefixName = $isPrefix ? self::currentCollection()->name : '';
        return App::package() . ':' . $prefixName . $name;
    }

    /**
     * build prefix name for collection
     *
     * @param $name
     * @return string
     */
    private static function buildPrefixNameCollection($name): string
    {
        $prefix = self::$current > -1 ? self::currentCollection()->name : '';
        return $prefix . $name;
    }

    /**
     * build prefix name for collection
     *
     * @param array $defaults
     * @return array
     */
    private static function buildDefaultsCollection(array $defaults): array
    {
        if (self::$current > -1) {
            $defaults = array_merge(self::currentCollection()->defaults, $defaults);
        }
        return $defaults;
    }

    /**
     * build prefix name for collection
     *
     * @param array $filters
     * @return array
     */
    private static function buildFiltersCollection(array $filters): array
    {
        if (self::$current > -1) {
            $filters = array_merge(self::currentCollection()->filters, $filters);
        }
        return $filters;
    }

    /**
     * build controller for collection
     *
     * @param mixed $controller
     * @return mixed
     */
    private static function buildControllerCollection(mixed $controller): mixed
    {
        if (self::$current > -1) {
            $controller = !empty($controller) ? $controller : self::currentCollection()->controller;
        }
        return $controller;
    }

    /**
     * build controller for collection
     *
     * @param string $path
     * @return mixed
     */
    private static function buildPrefixPathCollection(string $path): string
    {
        $prefix = self::$current > -1 ? self::currentCollection()->prefixPath : '';
        return $prefix . $path;
    }

    /**
     * get the main Collection
     *
     * @return Collection
     */
    public static function getMainCollection(): Collection
    {
        return self::$collections[0];
    }

    /**
     * get path
     *
     * @param $name
     * @param array $params
     * @return string
     * @throws Exception
     */
    public static function path($name, $params = []) : string
    {
        $name = App::package() . ':' . $name;
        return Container::pincore()->get('url_generator')->generate($name, $params);
    }
}