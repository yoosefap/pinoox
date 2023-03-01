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

namespace pinoox\component\manager;

use pinoox\component\kernel\Exception;
use pinoox\component\package\AppBuilder;
use pinoox\component\package\Package;
use pinoox\component\store\Pinker;
use Symfony\Component\Finder\Finder;

class AppManager
{
    /**
     * base path of apps
     *
     */
    private string $basePath;

    /**
     *  apps list
     *
     */
    private array $apps;

    /**
     * app
     *
     */
    private array $app;

    public function __construct()
    {
        $this->basePath = PINOOX_PATH . 'apps' . DS;
    }

    public function getApps(): array
    {
        $finder = new Finder();
        $finder->depth(0)->directories()->in($this->basePath);
        foreach ($finder as $folder) {
            $path = $folder->getRealPath();
            if (!file_exists($path . DS . 'app.php')) continue;

            $this->apps[] = [
                'path' => $path,
                'package' => $folder->getFilename(),
                'size' => $folder->getSize(),
            ];
        }

        return $this->apps;
    }

    /**
     * @throws \Exception
     */
    public function getApp($packageName): array|null
    {
        if ($packageName === Package::pincore) {
            $this->findPincore();
        } else {
            $this->findApp($packageName);
        }
        return $this->app;
    }

    /**
     * @throws \Exception
     */
    private function findApp($packageName): void
    {
        if (!is_dir($this->basePath . $packageName)) {
            throw new \Exception("The \"$packageName\" package could not found ");
        }

        $finder = new Finder();
        $finder->depth(0)->directories()->in($this->basePath . $packageName);
        $iterator = $finder->getIterator();
        $iterator->rewind();
        $folder = $iterator->current();
        $appPath = $folder->getPath() . DS . 'app.php';
        if (!file_exists($appPath)) {
            throw new \Exception('The "app.php" file could not found in "' . $packageName . '"');
        };

        $app = AppBuilder::init($packageName)->get();
        $app['path'] = $folder->getPath();
        $app['migration'] = $app['path'] . DS . $this->getMigrationPath($app);
        $app['namespace'] = $this->getNamespace($app);

        $this->app = $app;
    }

    private function findPincore(): void
    {
        $pincore['pinoox'] = Pinker::init('~pincore/pinoox.config')->pickup();
        $pincore['app'] = Pinker::init('~pincore/app.config')->pickup();
        $this->app = $pincore;
    }

    private function getMigrationPath($app): string
    {
        if (empty($app)) return false;
        return str_replace(['/', '\\'], DS, $app['migration']['path'] ?? 'database/migrations');
    }

    private function getNamespace($app): string
    {
        return 'pinoox' . DS . 'app' . DS . $app['package'];
    }


}

