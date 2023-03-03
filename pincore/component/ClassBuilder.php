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

namespace pinoox\component;

class ClassBuilder
{

    private static ClassBuilder $obj;
    private string $className;
    private ?string $extends;
    private string $namespace;
    private ?array $uses;
    private ?bool $copyright = true;
    private ?array $properties;
    private ?array $methods;
    private string $code;
    private bool $isSuccess;

    public function __construct($className)
    {
        $this->className = $className;
    }

    public static function init($className): ClassBuilder
    {
        self::$obj = new ClassBuilder($className);
        return self::$obj;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function extends($extends): ClassBuilder
    {
        $this->extends = $extends;
        return self::$obj;
    }

    public function namespace($namespace): ClassBuilder
    {
        $this->namespace = $namespace;
        return self::$obj;
    }

    public function use($use): ClassBuilder
    {
        $this->uses[] = $use;
        return self::$obj;
    }

    public function property($prop): ClassBuilder
    {
        $this->properties[] = $prop;
        return self::$obj;
    }

    public function method($modifier, $comment = null): ClassBuilder
    {
        $this->methods[$modifier] = [
            'comment' => $comment,
        ];
        return self::$obj;
    }

    public function copyright($copyright): ClassBuilder
    {
        $this->copyright = $copyright;
        return self::$obj;
    }

    public function build(): ClassBuilder
    {
        $this->code = "<?php \n";
        $this->code .= $this->copyright ? $this->makeCopyright() : '';
        $this->code .= !empty($this->namespace) ? $this->makeNameSpace() : '';
        $this->code .= !empty($this->uses) ? $this->makeUse() : '';
        $this->code .= $this->openClassDeclaration();
        $this->code .= $this->makeMethods();
        $this->code .= $this->closeClassDeclaration();

        return self::$obj;
    }

    /**
     * @throws \Exception
     */
    public function export($path) : ClassBuilder
    {
        if (!isset($this->code)){
            throw new \Exception('Before export you should call build method');
        }
        $this->isSuccess = File::generate($path, $this->code);
        return self::$obj;
    }

    private function makeCopyright(): string
    {
        $copyright = "/**\n";
        $copyright .= " *      ****  *  *     *  ****  ****  *    *\n";
        $copyright .= " *      *  *  *  * *   *  *  *  *  *   *  *\n";
        $copyright .= " *      ****  *  *  *  *  *  *  *  *    *\n";
        $copyright .= " *      *     *  *   * *  *  *  *  *   *  *\n";
        $copyright .= " *      *     *  *    **  ****  ****  *    *\n";

        $copyright .= " *\n";
        $copyright .= sprintf(" * @author   %s\n", 'Pinoox');
        $copyright .= sprintf(" * @link %s\n", 'https://www.pinoox.com');
        $copyright .= sprintf(" * @license  %s\n", 'https://opensource.org/licenses/MIT MIT License');
        $copyright .= " */\n\n";
        return $copyright;
    }

    private function makeNamespace(): string
    {
        return sprintf("namespace %s;\n\n", $this->namespace);
    }

    private function makeUse(): string
    {
        $uses = '';
        foreach ($this->uses as $use) {
            $uses .= sprintf("use %s;\n", $use);
        }
        return $uses . "\n";
    }

    private function openClassDeclaration(): string
    {
        return sprintf("class %s", $this->className)
            . (isset($this->extends) ? ' extends ' . $this->extends : '')
            . "\n{\n\n";
    }

    private function closeClassDeclaration(): string
    {
        return "}\n";
    }

    private function makeMethods(): string
    {
        $methods = '';
        if (!empty($this->methods)) {
            foreach ($this->methods as $modifier => $item) {
                $methods .= isset($item['comment']) ? "\t /** \n\t *" . sprintf(" %s", $item['comment']) . "\n\t *\t\n\t */ \n" : '';
                $methods .= "\t " . $modifier . " ()\n";
                $methods .= "\t{\n";
                $methods .= "\t\t\n";
                $methods .= "\t}\n\n";
            }
        }
        return $methods;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}