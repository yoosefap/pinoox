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


namespace pinoox\component\package\loader;


final class ArrayLoader implements LoaderInterface
{
    public function __construct(private array $packages)
    {
    }

    public function path(string $packageName): string
    {
        return $this->packages[$packageName];
    }

    public function exists(string $packageName): bool
    {
        return isset($this->packages[$packageName]);
    }
}