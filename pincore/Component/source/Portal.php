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


namespace pinoox\component\source;


use pinoox\component\helpers\HelperString;
use pinoox\component\kernel\Container;
use pinoox\component\kernel\ContainerBuilder;

abstract class Portal
{
    /**
     * register in container
     */
    public static function __register(): void
    {
    }

    /**
     * Get the registered name of the component.
     * @return string
     */
    abstract public static function __name(): string;

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        $instance = static::__instance();

        if (empty($instance)) {
            throw new \RuntimeException('A Portal root has not been set.');
        }

        return $instance->$method(...$args);
    }

    /**
     * instance object in container
     *
     * @return object|null
     */
    public static function __instance(): ?object
    {
        $name = static::__name();
        $container = HelperString::firstHas(static::class, 'pinoox\\app') ? Container::app() : Container::pincore();
        if (!empty($name) && $container->has($name)) {
            try {
                return $container->get($name);
            } catch (\Exception $e) {
            }
        }

        return null;
    }
}