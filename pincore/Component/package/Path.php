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


use Nette\FileNotFoundException;
use pinoox\component\package\AppReferenceInterface;
use pinoox\component\package\loader\LoaderInterface;
use pinoox\component\package\parser\AppNameParserInterface;

class Path
{
    /**
     * @var string[]
     */
    private array $paths = [];

    public function __construct(private AppNameParserInterface $parser, private LoaderInterface $loader, private string $packageName, private string $basePath)
    {
    }

    /**
     * Get path
     *
     * @param string $path
     * @return string
     */
    public function get(string|AppReferenceInterface $path = ''): string
    {
        if (is_string($path) && isset($this->paths[$path]))
            return $this->paths[$path];

        if ($path instanceof AppReferenceInterface)
            $parser = $path;
        else
            $parser = $this->parser->parse($path);

        $basePath = $this->getBasePath($parser->getPackageName());
        $value = !empty($parser->getPath()) ? $basePath . DIRECTORY_SEPARATOR . $parser->getPath() : $basePath;

        return $this->paths[$path] = $value;
    }

    public function set($key, $value): Path
    {
        $this->paths[$key] = $value;
        return $this;
    }

    private function getBasePath(?string $packageName = null): string
    {
        if ($packageName === '~') {
            $basePath = $this->basePath;
        } else if ($packageName === 'pincore') {
            $basePath = $this->basePath . 'pincore' . DIRECTORY_SEPARATOR;
        }
        else if (is_null($packageName) && $this->loader->exists($this->packageName)) {
            $basePath = $this->loader->path($this->packageName);
        }
        else if ($packageName && $this->loader->exists($packageName)) {
            $basePath = $this->loader->path($packageName);
        }
        else
        {
            throw new FileNotFoundException()
        }

        if (is_null($packageName)) {
            $basePath = $this->loader->path($this->packageName);
        } else if ($packageName === 'pincore') {
            $basePath = $this->basePath . 'pincore' . DIRECTORY_SEPARATOR;
        } else if ($packageName === '~') {
            $basePath = $this->basePath;
        } else if ($packageName && $this->loader->exists($packageName)) {
            $basePath = $this->loader->path($packageName);
        } else {
            if ($isBase = Str::firstHas($packageName, '~')) {
                $packageName = Str::firstDelete($packageName, '~');
                $basePath = $this->basePath . 'pincore';
                $basePath = $packageName === 'pincore' ? $basePath : $basePath . DIRECTORY_SEPARATOR . $packageName;
            } else {
                $basePath = $this->basePath . 'apps' . DIRECTORY_SEPARATOR . $this->packageName . DIRECTORY_SEPARATOR . $packageName;
            }
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