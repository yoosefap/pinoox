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


interface LoaderInterface
{
    /**
     * @param string $packageName
     * @return string
     * @throws \Exception
     */
    public function path(string $packageName): string;

    /**
     * @param string $packageName
     * @return bool
     */
    public function exists(string $packageName): bool;
}