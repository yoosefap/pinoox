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


namespace pinoox\component\helpers;


use pinoox\component\package\PathReferenceInterface;
use pinoox\component\package\loader\LoaderInterface;
use pinoox\component\package\parser\ParserInterface;

class Url
{
    public function __construct(private ParserInterface $parser, private LoaderInterface $loader, private string $packageName, private string $basePath)
    {
    }

    /**
     * Get path
     *
     * @param string $path
     * @return string
     */
    public function get(string|PathReferenceInterface $path = ''): string
    {
        if ($path instanceof PathReferenceInterface)
            $parser = $path;
        else
            $parser = $this->parser->parse($path);

        $basePath = $this->getBasePath($parser->getPackageName());
        return !empty($parser->getPath()) ? $basePath . DIRECTORY_SEPARATOR . $parser->getPath() : $basePath;
    }

    public function getBasePath($packageName): string
    {

        if (is_null($packageName))
            $basePath = $this->loader->path($this->packageName);
        else if ($packageName === '~')
            $basePath = $this->basePath;
        else if ($packageName && $this->loader->exists($packageName))
            $basePath = $this->loader->path($packageName);
        else {
            $basePath = $this->basePath . $packageName;
            $basePath = (is_dir($basePath)) ? $basePath : $this->basePath . 'apps' . DIRECTORY_SEPARATOR . $packageName;
        }

        return Str::lastDelete($basePath, DIRECTORY_SEPARATOR);
    }

    /**
     * Get path app
     *
     * @return string
     */
    private function app(): string
    {
        return $this->appPath;
    }

    /**
     * Get path app
     *
     * @return string
     */
    private function base(): string
    {
        return $this->basePath;
    }

    /**
     * Convert string path by a directory separator
     *
     * @param string $path
     * @return string|mixed
     */
    public static function ds($path)
    {
        return str_replace(['/', '\\', '>'], DIRECTORY_SEPARATOR, $path);
    }

}