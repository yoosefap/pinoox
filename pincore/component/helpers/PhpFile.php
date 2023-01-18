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

use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile as PhpFileNette;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Factory;
use Nette\PhpGenerator\PsrPrinter;
use PHPUnit\Framework\MockObject\MockMethod;
use PHPUnit\Framework\MockObject\ReflectionException;
use pinoox\component\File;
use pinoox\component\kernel\Container;
use pinoox\component\package\App;
use pinoox\component\source\Portal;
use ReflectionMethod;
use SebastianBergmann\Type\ReflectionMapper;

class PhpFile
{
    public static function source(bool $isCopyright = true): PhpFileNette
    {
        $source = new PhpFileNette();

        if ($isCopyright)
            self::setCopyright($source);

        return $source;
    }

    public static function addCommentMethods(string $serviceName, ClassType|ClassLike $class, PhpNamespace $namespace, string $packageName = '~')
    {
        if (App::exists($packageName)) {
            $container = Container::app($packageName);
        } else {
            $container = Container::pincore();
        }
        if ($container->hasDefinition($serviceName)) {
            $className = $container->getDefinition($serviceName)->getClass();
            $reflection = $container->getReflectionClass($className);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if ($method->getName() === '__construct')
                    continue;
                if ($method instanceof ReflectionMethod) {
                    $class->addComment(self::generateMethodComment($method, $namespace));
                }
            }
        }
    }


    public static function createPortal(string $path, string $className, string $serviceName, string $packageName = '', string $namespace = '')
    {
        $namespaceString = ($packageName == '~' || $packageName == 'pincore' || empty($packageName)) ? 'pinoox\portal' : $namespace . 'portal';
        $source = self::source();

        $namespace = $source->addNamespace($namespaceString);
        $namespace->addUse(Portal::class);

        $class = $namespace->addClass($className);

        self::addCommentMethods($serviceName, $class, $namespace,$packageName);

        $class
            ->setExtends(Portal::class);

        // add method in class
        $method = $class->addMethod('__name');
        $method->addComment('Get the registered name of the component.');
        $method->addComment('@return string');
        $method->setPublic()
            ->setStatic()
            ->setReturnType('string')
            ->addBody("return '{$serviceName}';");

        File::generate($path, $source);
    }

    public static function registerPortal(string $path, string $className, string $packageName)
    {
        $source = PhpFileNette::fromCode(file_get_contents($path));

        $namespaceItems = array_values($source->getNamespaces());
        $namespace = $namespaceItems[0];
        $classes = $namespace->getClasses();
        foreach ($classes as $class) {
            $portalName = $namespace->getName() . '\\' . $class->getName();
            if ($class->getName() === $className) {
                $className = $class->getName();
                $serviceName = call_user_func([$portalName, '__name']);
                $class->setComment('');
                self::addCommentMethods($serviceName, $class, $namespaceItems[0],$packageName);
            }
        }

        File::generate($path, $source);
    }

    private static function callMethodRegisterInPortal(string $path, ClassType $class, string $packageName, string $namespace, string $className)
    {
        $objectName = $namespace . '\\' . $className;
    }

    private static function getMethodBody($path, ReflectionMethod $reflectionMethod)
    {
        $regex = '/function.*\s*?{(.*)\}/ms';
        $body = File::getBetweenLine($path, $reflectionMethod->getStartLine() - 1, $reflectionMethod->getEndLine());
        preg_match_all($regex, $body, $matches, PREG_SET_ORDER, 0);
        return isset($matches[0][1]) ? $matches[0][1] : null;
    }

    private static function addMethodRegisterInPortal(ClassType $class, string $body)
    {
        // add method in class
        $method = $class->addMethod('__register');
        $method->addComment('register component.');
        $method->setPublic()
            ->setStatic()
            ->setReturnType('void')
            ->addBody($body);
    }

    private static function setCopyright(PhpFileNette $source): void
    {
        $copyright = '';
        $copyright .= "***  *  *     *  ****  ****  *    *\n";
        $copyright .= "  *  *  * *   *  *  *  *  *   *  *\n";
        $copyright .= "***  *  *  *  *  *  *  *  *    *\n";
        $copyright .= "     *  *   * *  *  *  *  *   *  *\n";
        $copyright .= "     *  *    **  ****  ****  *    *\n\n";
        $copyright .= sprintf("@author   %s\n", 'Pinoox');
        $copyright .= sprintf("@link %s\n", 'https://www.pinoox.com');
        $copyright .= sprintf("@license  %s\n", 'https://opensource.org/licenses/MIT MIT License');

        $source->setComment($copyright);
    }

    public static function generateMethodComment(ReflectionMethod $method, PhpNamespace $namespace, int &$num = 1): string
    {
        $return = (new ReflectionMapper)->fromReturnType($method);
        $return = !empty($return->asString()) ? $return->asString() : '';
        $args = str_replace("\n", '', self::getMethodParametersForDeclaration($method));
        $args = str_replace("array ()", '[]', $args);

        if (!empty($return) && class_exists($return)) {
            $returnType = 'ObjectPortal' . $num;
            $namespace->addUse($return, $returnType);
            $num++;
        } else {
            $returnType = $return;
        }

        $returnType = !empty($returnType) ? $returnType . ' ' : '';
        return HelperString::replaceData('@method static {return}{name}({args})', [
            'name' => $method->getName(),
            'return' => $returnType,
            'args' => $args,
        ]);
    }

    public static function getMethodParametersForDeclaration(ReflectionMethod $method): string
    {
        $parameters = [];
        $types = (new ReflectionMapper)->fromParameterTypes($method);

        foreach ($method->getParameters() as $i => $parameter) {
            $name = '$' . $parameter->getName();

            /* Note: PHP extensions may use empty names for reference arguments
             * or "..." for methods taking a variable number of arguments.
             */
            if ($name === '$' || $name === '$...') {
                $name = '$arg' . $i;
            }

            $default = '';
            $reference = '';
            $typeDeclaration = '';

            if (!$types[$i]->type()->isUnknown()) {
                $typeDeclaration = $types[$i]->type()->asString() . ' ';
            }

            if ($parameter->isPassedByReference()) {
                $reference = '&';
            }

            if ($parameter->isVariadic()) {
                $name = '...' . $name;
            } elseif ($parameter->isDefaultValueAvailable()) {
                $default = ' = ' . self::exportDefaultValueParameterMethod($parameter);
            } elseif ($parameter->isOptional()) {
                $default = ' = null';
            }

            $parameters[] = $typeDeclaration . $reference . $name . $default;
        }

        return implode(', ', $parameters);
    }

    public static function exportDefaultValueParameterMethod(\ReflectionParameter $parameter): string
    {
        try {
            $defaultValue = $parameter->getDefaultValue();

            if (!is_object($defaultValue)) {
                return (string)var_export($defaultValue, true);
            }

            $parameterAsString = $parameter->__toString();
            return (string)explode(
                ' = ',
                substr(
                    substr(
                        $parameterAsString,
                        strpos($parameterAsString, '<optional> ') + strlen('<optional> ')
                    ),
                    0,
                    -2
                )
            )[1];
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        // @codeCoverageIgnoreEnd
    }

    public static function getMethodParametersForCall(ReflectionMethod $method): string
    {
        $parameters = [];

        foreach ($method->getParameters() as $i => $parameter) {
            $name = '$' . $parameter->getName();

            /* Note: PHP extensions may use empty names for reference arguments
             * or "..." for methods taking a variable number of arguments.
             */
            if ($name === '$' || $name === '$...') {
                $name = '$arg' . $i;
            }

            if ($parameter->isVariadic()) {
                continue;
            }

            if ($parameter->isPassedByReference()) {
                $parameters[] = '&' . $name;
            } else {
                $parameters[] = $name;
            }
        }

        return implode(', ', $parameters);
    }


}