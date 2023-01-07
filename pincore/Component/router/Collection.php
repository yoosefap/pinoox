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

use pinoox\component\helpers\HelperString;
use pinoox\component\package\App;

class Collection
{
    public RouteCollection $routes;

    public function __construct(
        public string $path = '',
        public string $prefixPath = '',
        public int $cast = -1,
        public mixed $controller = null,
        public string|array $methods = '',
        public mixed $action = null,
        public array $defaults = [],
        public array $filters = [],
        public string $name = '',
    )
    {

        $this->controller = $this->buildController($controller);
        if (is_string($methods) && !empty($methods)) {
            $methods = HelperString::multiExplode(['|', ',', '-'], $methods);
        }
        $this->methods = is_array($methods) ? $methods : [];
        $this->routes = new RouteCollection();
    }

    public function get(): RouteCollection
    {
        return $this->routes;
    }

    public function buildController($controller)
    {
        if (is_string($controller) && !class_exists($controller) && !HelperString::firstHas($controller, 'pinoox')) {
            $controller = 'pinoox\\app\\' . App::package() . '\\controller\\' . $controller;
        }

        return $controller;
    }

    public function buildMethods($methods): array
    {
        if (is_string($methods)) {
            $methods = HelperString::multiExplode(['|', ',', '-'], $methods);
        }

        $methods = is_array($methods) ? array_filter($methods) : [];
        $methods = !empty($methods) ? $methods : $this->methods;
        return $methods;
    }

    public function buildAction($action)
    {
        if (is_string($action) || is_array($action)) {
            if (is_string($action))
                $parts = HelperString::multiExplode(['@', '::', ':'], $action);
            else
                $parts = $action;

            $countParts = count($parts);
            if ($countParts == 1 && !empty($this->controller)) {
                $class = $this->controller;
                $method = $parts[0];
                return [$class, $method];
            } else if ($countParts == 2) {
                $class = $this->buildController($parts[0]);
                $method = $parts[1];
                return [$class, $method];
            }
        }

        return $action;
    }
}