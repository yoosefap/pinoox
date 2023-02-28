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

use Symfony\Component\Finder\Finder;

class AppManager
{
    /**
     * base path of apps
     *
     */
    private string $basePath;
    private string $path;

    /**
     * installed apps list
     *
     */
    private array $apps;

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
        $finder = new Finder();
        $finder->depth(0)->directories()->in($this->basePath . $packageName);
        $iterator = $finder->getIterator();
        $iterator->rewind();
        $folder = $iterator->current();
        $appPath = $folder->getPath() . DS . 'app.php';
        if (!file_exists($appPath)) {
            throw new \Exception('The "app.php" file could not found in "' . $packageName . '"');
        };

        $app = include($appPath);
        $app['path'] = $folder->getPath();
        $app['migration'] = $app['path']  .DS. $this->getMigrationPath($app);
        $app['namespace'] = $this->getNamespace($app);

        return $app;
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

