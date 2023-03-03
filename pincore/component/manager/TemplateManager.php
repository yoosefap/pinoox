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

use Nette\Utils\Finder;
use pinoox\component\File;

class TemplateManager
{
    /**
     * base path of apps
     *
     */
    private string $basePath;

    /**
     * package name
     *
     */
    private string $packageName;

    /**
     * installed apps list
     *
     */
    private array $apps;

    public function __construct()
    {
        $this->basePath = getcwd() . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR;

    }

    /**
     * set app's package name
     *
     * @param $packageName
     */
    public function package($packageName): void
    {
        $this->packageName = $packageName;
    }


}

