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

use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile as PhpFileNette;
use Nette\PhpGenerator\PhpNamespace;
use pinoox\component\File;
use pinoox\component\helpers\HelperString;
use pinoox\component\helpers\Str;
use pinoox\component\kernel\Container;
use pinoox\component\package\App;
use pinoox\component\source\Portal;
use ReflectionFunction;
use ReflectionMethod;

class PortalFile extends PhpFile
{

    public static function createPortal(string $path, string $className, string $serviceName, string $packageName = '', string $namespace = ''): void
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

    public static function updatePortal(string $path, string $className, string $packageName): void
    {
        $source = PhpFileNette::fromCode(file_get_contents($path));

        $namespaceItems = array_values($source->getNamespaces());
        $namespace = $namespaceItems[0];
        $classes = $namespace->getClasses();
        foreach ($classes as $class) {
            $portalName = $namespace->getName() . '\\' . $class->getName();
            if ($class->getName() === $className) {
                $serviceName = call_user_func([$portalName, '__name']);
                $class->setComment('');
                self::addCommentMethods($portalName, $serviceName, $class, $namespaceItems[0], $packageName);
            }
        }

        File::generate($path, $source);
    }

    private static function addMethodExclude(ClassType $class): void
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

    private static function addMethodCallBack(ClassType $class, array $items = []): void
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

    private static function addMethodName(ClassType $class, $serviceName): void
    {
        $serviceName = Str::camelToUnderscore($serviceName, '.');
        $method = $class->addMethod('__name');
        $method->addComment('Get the registered name of the component.');
        $method->addComment('@return string');
        $method->setPublic()
            ->setStatic()
            ->setReturnType('string')
            ->addBody("return '{$serviceName}';");
    }

    private static function addMethodRegisterInPortal(ClassType $class, string $body): void
    {
        // add method in class
        $method = $class->addMethod('__register');
        $method->addComment('register component.');
        $method->setPublic()
            ->setStatic()
            ->setReturnType('void')
            ->addBody($body);
    }

    public static function generateMethodComment(string $name, string $className, string $serviceName, string $methodName, ReflectionFunction|ReflectionMethod $method, PhpNamespace $namespace, string $return, bool $isCallBack = true, array $callback = [], int &$num = 1): string
    {

        if (($return === 'void' || in_array($methodName, $callback)) && $isCallBack) {
            $return = $className;
        }

        $args = str_replace("\n", '', self::getMethodParametersForDeclaration($method));
        $args = str_replace("array ()", '[]', $args);
        if ($return === $name) {
            $returnType = $className;
        } else if ($return === 'static') {
            $returnType = '\\' . $serviceName;
        } else if (!empty($return) && class_exists($return) || interface_exists($return) || trait_exists($return)) {
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
        if (Str::has($returnType, '?')) {
            $returnType = str_replace('?', '', $returnType);
            if (!Str::has($returnType, 'null') || !Str::has($returnType, 'NULL')) {
                $returnType .= '|null';
            }
        }
        $returnType = !empty($returnType) ? $returnType . ' ' : '';
        return HelperString::replaceData('@method static {return}{name}({args})', [
            'name' => $methodName,
            'return' => $returnType,
            'args' => $args,
        ]);
    }

    public static function addCommentMethods(string $name, string $serviceName, ClassType|ClassLike $class, PhpNamespace $namespace, string $packageName = '~'): void
    {
        $isCallBack = true;
        $callback = [];
        $include = [];
        $exclude = [];
        $replace = [];

        if (class_exists($name)) {
            $isCallBack = call_user_func([$name, '__isCallBack']);
            $callback = call_user_func([$name, '__callback']);
            $exclude = call_user_func([$name, '__exclude']);
            $include = call_user_func([$name, '__include']);
            $replace = call_user_func([$name, '__compileReplaces']);
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
                if (isset($replace[$method->getName()]) || HelperString::firstHas($method->getName(), '__') || in_array($method->getName(), $exclude) || (!empty($include) && !in_array($method->getName(), $include)))
                    continue;
                if ($method instanceof ReflectionMethod) {
                    $returnType = self::getReturnTypeMethod($method);
                    if ($returnType === 'void' && $isCallBack && empty($callback)) {
                        $voidMethods[] = $method->getName();
                    }
                    $class->addComment(self::generateMethodComment($name, $class->getName(), $className, $method->getName(), $method, $namespace, $returnType, $isCallBack, $callback, $num));
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
                $class->addComment(self::generateMethodComment($name, $class->getName(), $className, $methodName, $func, $namespace, $returnType, $isCallBack, $callback, $num));
            }

            if (empty($callback))
                self::addMethodCallBack($class, $voidMethods);

            $class->addComment('@method static \\' . $className . ' object()');
            $class->addComment("\n" . '@see \\' . $className);
        }
    }

}