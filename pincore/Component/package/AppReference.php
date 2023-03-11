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


namespace pinoox\component\package;


use pinoox\component\package\loader\LoaderInterface;

class AppReference implements AppReferenceInterface
{

    /**
     * create stack
     *
     * @param string|null $packageName
     * @param string|null $path
     */
    public function __construct(private ?string $packageName,private ?string $path = null)
    {
    }

    /**
     * get package name
     *
     * @return string|null
     */
    public function getPackageName(): ?string
    {
        return $this->packageName;
    }

    /**
     * get package name
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}