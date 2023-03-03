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
use pinoox\component\Zip;

class AppFileHandler
{

    public function install($file): void
    {
    }

    public function export($path): void
    {
    }

    public function findDirectories($path)
    {
        return Finder::findDirectories($path);
    }

}

