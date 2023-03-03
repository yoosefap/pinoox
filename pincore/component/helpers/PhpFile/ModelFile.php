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


namespace pinoox\component\helpers\PhpFile;

use pinoox\component\database\Model;
use pinoox\component\File;

class ModelFile extends PhpFile
{

    public static function create(string $exportPath, string $className, string $package, string $namespace): bool
    {
        $source = self::source();

        $namespace = $source->addNamespace($namespace);
        $namespace->addUse(Model::class);

        $class = $namespace->addClass($className);
        $class->setExtends(Model::class);

        return File::generate($exportPath, $source);
    }


}