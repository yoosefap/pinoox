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

use Illuminate\Database\Schema\Blueprint;
use Nette\PhpGenerator\ClassType;
use pinoox\component\File;
use pinoox\component\helpers\Str;
use pinoox\component\migration\MigrationBase;

class MigrationFile extends PhpFile
{

    public static function create(string $exportPath, string $className, string $package): bool
    {
        //$namespaceString = ($packageName == '~' || $packageName == 'pincore' || empty($packageName)) ? 'pinoox\portal' : $namespace . 'portal';
        $namespaceStr = "pinoox\\app\\" . $package . "\\database\\migrations";
        $source = self::source();

        $namespace = $source->addNamespace($namespaceStr);
        $namespace->addUse(Blueprint::class);
        $namespace->addUse(MigrationBase::class);

        $class = $namespace->addClass($className);
        $class->setExtends(MigrationBase::class);
        self::addRunMethod($class, $className);
        self::addDownMethod($class, $className);

        return File::generate($exportPath, $source);
    }

    private static function addRunMethod(ClassType $class, string $className): void
    {
        $tableName = Str::camelToUnderscore($className, '_');
        $method = $class->addMethod('up');
        $method->addComment('Run the migrations.');
        $method->setPublic()
            ->setReturnType('void')
            ->addBody('$this->schema->create("' . $tableName . '", function (Blueprint $table) {')
            ->addBody("\t" . '$table->increments("id");' . "\n" . '});');
    }

    private static function addDownMethod(ClassType $class, string $className): void
    {
        $tableName = Str::camelToUnderscore($className, '_');
        $method = $class->addMethod('down');
        $method->addComment('Reverse the migrations.');
        $method->setPublic()
            ->setReturnType('void')
            ->addBody('$this->schema->dropIfExists("' . $tableName . '");');
    }

}