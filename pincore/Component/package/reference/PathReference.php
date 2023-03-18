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


namespace pinoox\component\package\reference;


use pinoox\component\package\loader\LoaderInterface;

class PathReference implements PathReferenceInterface
{

    /**
     * create stack
     *
     * @param string|null $packageName
     * @param string|null $path
     */
    public function __construct(private ?string $packageName, private ?string $path = null)
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

    /**
     * create path reference
     *
     * @param string|null $packageName
     * @param string|null $path
     * @return static
     */
    public static function create(?string $packageName, ?string $path = null): static
    {
        return new static($packageName, $path);
    }

    public function get(): ?string
    {
        $package = $this->getPackageName();

        return !empty($package) ? $package . ':' . $this->getPath() : $this->getPath();
    }
}