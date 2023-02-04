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
use Nette\PhpGenerator\PhpFile as PhpFileNette;
use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\MockObject\ReflectionException;
use pinoox\component\File;
use pinoox\component\kernel\Container;
use pinoox\component\package\App;
use pinoox\component\source\Portal;
use ReflectionFunction;
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

    public static function addCommentMethods(string $portalName, string $serviceName, ClassType|ClassLike $class, PhpNamespace $namespace, string $packageName = '~')
    {
        $isCallBack = true;
        $callback = [];
        $exclude = [];
        $replace = [];

        if (class_exists($portalName)) {
            $isCallBack = call_user_func([$portalName, '__isCallBack']);
            $callback = call_user_func([$portalName, '__callback']);
            $exclude = call_user_func([$portalName, '__exclude']);
            $replace = call_user_func([$portalName, '__compileReplaces']);
        }

        if (App::exists($packageName)) {
            $container = Container::app($packageName);
        } else {
            $container = Container::pincore();
        }
        if ($container->hasDefinition($serviceName)) {
            $voidMethods = [];
            $num = 1;
            $className = $container->getDefinition($serviceName)->getClass();
            $reflection = $container->getReflectionClass($className);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if (isset($replace[$method->getName()]) || HelperString::firstHas($method->getName(), '__') || in_array($method->getName(), $exclude))
                    continue;
                if ($method instanceof ReflectionMethod) {
                    $returnType = self::getReturnTypeMethod($method);
                    if ($returnType === 'void' && $isCallBack && empty($callback)) {
                        $voidMethods[] = $method->getName();
                    }
                    $class->addComment(self::generateMethodComment($portalName, $class->getName(), $method->getName(), $method, $namespace, $returnType, $isCallBack, $callback, $num));
                }
            }

            foreach ($replace as $methodName => $closure) {
                try {
                    $func = new ReflectionFunction($closure);
                } catch (\ReflectionException $e) {
                    continue;
                }


                if (in_array($methodName, $exclude))
                    continue;
                $returnType = self::getReturnTypeMethod($func);
                if ($returnType === 'void' && $isCallBack && empty($callback)) {
                    $voidMethods[] = $methodName;
                }
                $class->addComment(self::generateMethodComment($portalName, $class->getName(), $methodName, $func, $namespace, $returnType, $isCallBack, $callback, $num));
            }

            if (empty($callback))
                self::addMethodCallBack($class, $voidMethods);

            $class->addComment('@method static \\' . $className . ' object()');
            $class->addComment("\n" . '@see \\' . $className);
        }
    }


    public static function createPortal(string $path, string $className, string $serviceName, string $packageName = '', string $namespace = '')
    {
        $namespaceString = ($packageName == '~' || $packageName == 'pincore' || empty($packageName)) ? 'pinoox\portal' : $namespace . 'portal';
        $source = self::source();

        $namespace = $source->addNamespace($namespaceString);
        $namespace->addUse(Portal::class);
        $portalName = $namespaceString . '\\' . $className;

        $class = $namespace->addClass($className);
        $class->setExtends(Portal::class);
        self::addMethodName($class, $serviceName);
        self::addMethodCallBack($class);
        self::addMethodExclude($class);

        self::addCommentMethods($portalName, $serviceName, $class, $namespace, $packageName);
        File::generate($path, $source);
    }

    private static function addMethodCallBack(ClassType $class, array $items = [])
    {
        if (!empty($items)) {
            $items = implode("',\n\t'", $items);
            $body = "return [\n\t'" . $items . "'\t\n];";
        } else {
            $body = "return [];";
        }


        if ($class->hasMethod('__callback')) {
            $class->removeMethod('__callback');
        }

        $method = $class->addMethod('__callback');
        $method->addComment('Get method names for callback object.');
        $method->addComment('@return string[]');
        $method->setPublic()
            ->setStatic()
            ->setReturnType('array')
            ->addBody($body);
    }

    private static function addMethodExclude(ClassType $class)
    {
        if (!$class->hasMethod('__exclude')) {
            $method = $class->addMethod('__exclude');
            $method->addComment('Get exclude method names .');
            $method->addComment('@return string[]');
            $method->setPublic()
                ->setStatic()
                ->setReturnType('array')
                ->addBody("return [];");
        }
    }

    private static function addMethodName(ClassType $class, $serviceName)
    {
        $method = $class->addMethod('__name');
        $method->addComment('Get the registered name of the component.');
        $method->addComment('@return string');
        $method->setPublic()
            ->setStatic()
            ->setReturnType('string')
            ->addBody("return '{$serviceName}';");
    }

    public static function updatePortal(string $path, string $className, string $packageName)
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
                self::addCommentMethods($portalName, $serviceName, $class, $namespaceItems[0], $packageName);

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

    public static function getReturnTypeMethod($method): string
    {
        $return = (new ReflectionMapper)->fromReturnType($method);
        return !empty($return->asString()) ? $return->asString() : '';
    }

    public static function generateMethodComment(string $portalName, string $portalClassName, string $methodName, ReflectionFunction|ReflectionMethod $method, PhpNamespace $namespace, string $return, bool $isCallBack = true, array $callback = [], int &$num = 1): string
    {

        if (($return === 'void' || in_array($methodName, $callback)) && $isCallBack) {
            $return = $portalClassName;
        }

        $args = str_replace("\n", '', self::getMethodParametersForDeclaration($method));
        $args = str_replace("array ()", '[]', $args);
        if ($return === $portalName) {
            $returnType = $portalClassName;
        } else if (!empty($return) && class_exists($return)) {
            if ($use = self::getUse($namespace, $return)) {
                $returnType = $use;
            } else {
                $returnType = 'ObjectPortal' . $num;
                $namespace->addUse($return, $returnType);
                $num++;
            }

        } else {
            $returnType = $return;
        }

        $returnType = !empty($returnType) ? $returnType . ' ' : '';
        return HelperString::replaceData('@method static {return}{name}({args})', [
            'name' => $methodName,
            'return' => $returnType,
            'args' => $args,
        ]);
    }

    private static function hasUse(PhpNamespace $namespace, $class): bool
    {
        $uses = $namespace->getUses();
        return in_array($class, $uses);
    }

    private static function getUse(PhpNamespace $namespace, $class)
    {
        $uses = $namespace->getUses();
        return array_search($class, $uses);
    }

    public static function getMethodParametersForDeclaration(ReflectionFunction|ReflectionMethod|\Closure $method): string
    {
        if ($method instanceof \Closure) {
            try {
                $method = new ReflectionFunction($method);
            } catch (\ReflectionException $e) {
                return '';
            }
        }

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
                $typesString = $types[$i]->type()->asString();
                $typesItems = explode('|', $typesString);
                foreach ($typesItems as $key => $typesItem) {
                    if (class_exists($typesItem))
                        $typesItems[$key] = '\\' . $typesItem;
                }
                $typesString = implode('|', $typesItems);
                $typeDeclaration = $typesString . ' ';
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