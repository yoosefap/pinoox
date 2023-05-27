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


namespace pinoox\component\package\engine;


use pinoox\component\package\App;
use pinoox\component\package\loader\ArrayLoader;
use pinoox\component\package\loader\ChainLoader;
use pinoox\component\package\loader\LoaderInterface;
use pinoox\component\package\loader\PackageLoader;
use pinoox\component\package\reference\ReferenceInterface;
use pinoox\component\router\Router;
use pinoox\component\store\config\Config;

class AppEngine implements EngineInterface
{
    private LoaderInterface $loader;
    private PackageLoader $packageLoader;
    private ArrayLoader $arrayLoader;

    /**
     * AppEngine constructor.
     *
     * @param string $pathApp
     * @param array $defaultData
     * @param string $appFile
     * @param string $folderPinker
     */
    public function __construct(string $pathApp, private string $appFile, private string $folderPinker, private array $defaultData = [])
    {
        $this->arrayLoader = new ArrayLoader($appFile);
        $this->packageLoader = new PackageLoader($appFile, $pathApp);

        $this->loader = new ChainLoader([
            $this->arrayLoader,
            $this->packageLoader,
        ]);
    }

    public function routes(ReferenceInterface|string $packageName): Router
    {
        $routes = $this->config($packageName)->get('router.routes');
        $path = App::path();
        $router = new Router();
        $router->collection('ttt/', $routes);
        return $router;
    }

    /**
     * Get config app.
     *
     * @param ReferenceInterface|string $packageName
     * @return Config
     * @throws \Exception
     */
    public function config(ReferenceInterface|string $packageName): Config
    {
        $path = $this->loader->path($packageName);
        $mainFile = $this->ds($path . '/' . $this->appFile);
        $bakedFile = $this->ds($path . '/' . $this->folderPinker . '/' . $this->appFile);
        $pinker = new Pinker($mainFile, $bakedFile);
        $pinker->dumping(true);
        return new Config($pinker, $this->defaultData);
    }

    /**
     * replace directory separator
     *
     * @param string $string
     * @return string
     */
    private function ds(string $string): string
    {
        return str_replace(['/', '\\', '>'], DIRECTORY_SEPARATOR, $string);
    }

    /**
     * Exists app.
     *
     * @param ReferenceInterface|string $packageName
     * @return bool
     */
    public function exists(ReferenceInterface|string $packageName): bool
    {
        return $this->loader->exists($packageName);
    }

    /**
     * add app
     *
     * @param $packageName
     * @param $path
     */
    public function add($packageName, $path): void
    {
        $this->arrayLoader->add($packageName, $path);
    }

    /**
     * Supports app.
     *
     * @param ReferenceInterface|string $packageName
     * @return bool
     */
    public function supports(ReferenceInterface|string $packageName): bool
    {
        return $this->checkName($packageName) && $this->exists($packageName);
    }

    /**
     * Get path app.
     *
     * @param ReferenceInterface|string $packageName
     * @return string
     * @throws \Exception
     */
    public function path(ReferenceInterface|string $packageName): string
    {
        return $this->loader->path($packageName);
    }

    /**
     * Check valid package name
     *
     * @param $packageName
     * @return bool
     */
    public function checkName($packageName): bool
    {
        return !!preg_match('/^[a-zA-Z]+[a-zA-Z0-9]*+[_]\s{0,1}[a-zA-Z0-9]+[_]\s{0,1}[a-zA-Z0-9]+[_]{0,1}[a-zA-Z0-9]+$/m', $packageName);
    }
}